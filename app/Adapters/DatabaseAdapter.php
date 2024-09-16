<?php

namespace App\Adapters;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToMoveFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;

class DatabaseAdapter implements FilesystemAdapter
{
    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            return Storage::disk('local')->exists($path);
        }

        $this->validatePath($path);

        return File::where('name', $path)->count() == 1;
    }

    /**
     * @inheritDoc
     */
    public function directoryExists(string $path): bool
    {
        // como estamos armazenando arquivos no banco de dados não há estrutura de diretório
        return false;
    }

    /**
     * @inheritDoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->validatePath($path);

        // Checa se o conteúdo está vazio
        if (!$contents) {
            throw new UnableToWriteFile("Parece ser um arquivo vazio ou não foi possível ler o conteúdo");
        }

        // Verifica se já existe um arquivo com o mesmo caminho
        if (File::where('name', $path)->count() > 0) {
            throw new UnableToWriteFile("Já existe um arquivo nesse caminho: {$path}");
        }

        // Cria um novo registro de arquivo
        $file = new File();
        $file->hash = Str::orderedUuid();
        $file->name = $path;
        $file->content = base64_encode($contents);  // Encode do conteúdo
        $file->size = strlen($contents);  // Tamanho do conteúdo
        $file->mime_type = $this->mime_content_type_from_string($contents);  // Usar uma função personalizada para strings
        $file->save();
    }

    protected function mime_content_type_from_string(string $contents): ?string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($contents);
    }

    /**
     * @inheritDoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            // Define o nome do arquivo
            $fileName = basename($path);
            // Define o caminho completo no diretório temporário
            $tempFilePath = 'livewire-tmp/' . $fileName;

            // Salva o conteúdo do stream no diretório temporário
            $content = stream_get_contents($contents);
            if ($content === false) {
                throw new UnableToWriteFile("Não foi possível ler o conteúdo do stream");
            }
            // Usa o disco local para salvar o arquivo
            Storage::disk('local')->put($tempFilePath, $content);
            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function read(string $path): string
    {
        $this->validatePath($path);

        $file = File::where('name', $path);

        if (($file->count()) != 1)
            throw new UnableToReadFile("Não foi possível localizar o arquivo {$path}");

        return base64_decode(stream_get_contents($file->first()->content));
    }

    /**
     * @inheritDoc
     */
    public function readStream(string $path)
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            // Verifica se o arquivo existe no diretório temporário
            if (Storage::disk('local')->exists($path)) {
                // Obtém o conteúdo do arquivo
                $contents = Storage::disk('local')->get($path);

                // Retorna o conteúdo diretamente (ou cria um stream se realmente necessário)
                return $contents;
            } else {
                throw new UnableToReadFile("Não é possível encontrar o arquivo {$path}.");
            }
        }

        $this->validatePath($path);
        $contents = $this->read($path);
        $writeStream = fopen('php://temp', 'w+b');
        fwrite($writeStream, $contents);
        rewind($writeStream);
        return $writeStream;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $path): void
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            // Verifica se o arquivo existe no diretório temporário
            if (Storage::disk('local')->exists($path)) {
                // Obtém o conteúdo do arquivo
                Storage::disk('local')->delete($path);
                return;
            } else {
                throw new UnableToReadFile("Não é possível encontrar o arquivo {$path}.");
            }
        }

        $this->validatePath($path);

        File::where('name', $path)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path): void
    {
        dd('deleteDirectory', $path);
        $this->validatePath($path);

        File::where('name', $path)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        throw UnableToCreateDirectory::atLocation($path, 'O adaptador não suporta diretórios fora do caminho do arquivo; adicione um arquivo em vez disso.');
    }

    /**
     * @inheritDoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        throw UnableToSetVisibility::atLocation($path, 'O adaptador não suporta controles de visibilidade.');
    }

    /**
     * @inheritDoc
     */
    public function visibility(string $path): FileAttributes
    {
        throw UnableToSetVisibility::atLocation($path, 'O adaptador não suporta controles de visibilidade.');
    }

    /**
     * @inheritDoc
     */
    public function mimeType(string $path): FileAttributes
    {
        $this->validatePath($path);

        $file = File::where('name', $path)->first();

        if (!$file)
            throw new UnableToReadFile("Não é possível encontrar o arquivo {$path}");

        return new FileAttributes(
            $path,
            null,
            null,
            null,
            $file->mime_type
        );
    }

    /**
     * @inheritDoc
     */
    public function lastModified(string $path): FileAttributes
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            $timestamp = Storage::disk('local')->lastModified($path);

            // Retorna um objeto FileAttributes com o timestamp
            return new FileAttributes(
                $path,
                null,
                null,
                $timestamp
            );
        }

        $this->validatePath($path);

        $file = File::where('name', $path)->first();

        if (($file->count()) != 1)
            throw new UnableToReadFile("Não é possível encontrar o arquivo {$path}");

        return new FileAttributes(
            $path,
            null,
            null,
            $file->updated_at
        );
    }

    /**
     * @inheritDoc
     */
    public function fileSize(string $path): FileAttributes
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp')) {
            $size = Storage::disk('local')->size($path);

            // Retorna um objeto FileAttributes com o tamanho
            return new FileAttributes(
                $path,
                $size
            );
        }

        $this->validatePath($path);

        $file = File::where('name', $path)->first();

        if (!$file)
            throw new UnableToReadFile("Não é possível encontrar o arquivo {$path}");

        return new FileAttributes(
            $path,
            $file->size
        );
    }

    /**
     * @inheritDoc
     */
    public function listContents(string $path, bool $deep): iterable
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp')) {
            // Define o caminho do diretório temporário
            $tempFiles = Storage::disk('local')->files($path);

            $fileAttributes = [];
            foreach ($tempFiles as $file) {
                $fileAttributes[] = new FileAttributes(
                    $file,
                    Storage::disk('local')->size($file),
                    null,
                    null,
                    mime_content_type(Storage::disk('local')->path($file))
                );
            }

            return $fileAttributes;
        }

        $this->validatePath($path);

        $file = File::where('name', $path)
            ->get();

        if ($file->count() <= 0)
            throw new UnableToListContents("Não é possível encontrar o caminho {$path}");

        $retArr = [];
        foreach ($file as $b) {
            $retArr[] = (new FileAttributes(
                $b->name,
                $b->size ?? null,
                null,
                $b->updated_at->timestamp,
                $b->mime_type
            ));
        }

        return $retArr;
    }

    /**
     * @inheritDoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        dd('move', $source, $destination, $config);
        // valida.
        $this->validatePath($source);
        $this->validatePath($destination);

        // Encontre o(s) registro(s).
        $srcFile = File::where('name', $source);
        $dstFile = File::where('name', $destination);

        // Há colisões?
        if ($dstFile->count() != 0)
            throw UnableToMoveFile::fromLocationTo($source, $destination);

        // Há algo para mover?
        if ($srcFile->count() == 0)
            throw UnableToMoveFile::fromLocationTo($source, $destination);

        // Atualizar nome.
        $srcFile->update(['name' => $destination]);
    }

    /**
     * @inheritDoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        dd('copy', $source, $destination, $config);
        // valida.
        $this->validatePath($source);
        $this->validatePath($destination);

        // Encontre o(s) registro(s).
        $srcFile = File::where('name', $source);
        $dstFile = File::where('name', $destination);

        // Há colisões?
        if ($dstFile->count() != 0)
            throw UnableToCopyFile::fromLocationTo($source, $destination);

        // Há algo para mover?
        if ($srcFile->count() == 0)
            throw UnableToCopyFile::fromLocationTo($source, $destination);

        $copy = $srcFile->first()->replicate();
        $copy->name = $destination;
        $copy->save();
    }

    protected function validatePath(string $path)
    {
        // Verifica se o caminho contém o diretório temporário do Livewire
        if (str_contains($path, 'livewire-tmp/')) {
            // Extrai apenas o nome do arquivo, ignorando diretórios
            $fileName = basename($path);
            // Valida se o nome do arquivo contém uma extensão
            if (!preg_match('/\.[a-zA-Z0-9]+$/', $fileName)) {
                throw new \InvalidArgumentException("Este adaptador requer uma extensão para o arquivo: {$fileName}");
            }
            return;
        }

        // Valida se o caminho contém um diretório qualquer
        if (str_contains($path, '/')) {
            throw new \InvalidArgumentException("Este adaptador não suporta pastas no caminho: {$path}");
        }

        // Valida se o caminho contém uma extensão de arquivo
        if (!preg_match('/\.[a-zA-Z0-9]+$/', $path)) {
            throw new \InvalidArgumentException("Este adaptador requer uma extensão para o arquivo: {$path}");
        }
    }

    public function getUrl(string $path): string
    {
        return route('files.serve', ['name' => $path]);
    }
}
