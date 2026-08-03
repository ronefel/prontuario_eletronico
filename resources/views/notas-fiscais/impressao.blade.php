<!DOCTYPE html>
<html moznomarginboxes="" mozdisallowselectionprint="" class="js no-touch csstransforms3d csstransitions">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>NFS-e {{ $dados['numero_nfse'] }} {{ $dados['codigo_verificacao'] }} | Impressão</title>

	<link rel="stylesheet" type="text/css" href="{{ asset('css/nota-eletronica.css') }}">

	<style media="print">
		@page {
			size: A4;
			margin: 2mm;
		}

		#nota-eletronica-html,
		#nota-eletronica-html * {
			box-sizing: border-box;
		}

		#nota-eletronica-html {
			width: 206mm !important;
			max-width: 206mm !important;
		}

		#descricao-nota-eletronica {
			min-height: 205px !important;
		}

		#serico-prestado {
			height: 12mm !important;
			max-height: 12mm !important;
		}

		#outras-informacoes {
			height: 14mm !important;
			max-height: 14mm !important;
		}
	</style>

	<style>
		.hidden-print {
			box-shadow: none !important;
		}

		.painel-acoes-impressao {
			text-align: center;
			margin: 15px auto;
		}

		.btn-imprimir-nota {
			background-color: #0284c7;
			color: #ffffff;
			border: none;
			padding: 10px 24px;
			font-size: 14px;
			font-weight: bold;
			border-radius: 6px;
			cursor: pointer;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
		}

		.btn-imprimir-nota:hover {
			background-color: #0369a1;
		}

		.fundo-selo {
			margin-left: 10px;
			padding: 10px;
			border: 1px solid #999;

			/* Configurações do fundo cinza */
			background-color: #f2f2f2 !important;
			/* Forçado para o tom correto */
			background-image: repeating-linear-gradient(45deg,
					transparent,
					transparent 2px,
					rgba(0, 0, 0, 0.05) 2px,
					rgba(0, 0, 0, 0.05) 4px) !important;

			/* FORÇA A IMPRESSÃO DO FUNDO (Adicione estas 3 linhas) */
			-webkit-print-color-adjust: exact !important;
			print-color-adjust: exact !important;
			color-adjust: exact !important;
		}
	</style>
</head>

<body>
	<div class="painel-acoes-impressao hidden-print">
		<button type="button" class="btn-imprimir-nota" onclick="window.print()">
			🖨️ Imprimir Nota Fiscal
		</button>
	</div>

	<div id="conteudo-principal-do-sistema" class="container-fluid fluid menu-left">
		<div id="wrapper">
			<div id="content">
				<div class="innerLR">
					<div class="widget-body">
						<div class="tab-content" id="nota-eletronica-visualizar">
							<div id="nota-eletronica-html" style="position:relative;">
								<div>
									<div class="impressao">
										<div class="conteudo">

											@if($dados['eh_rascunho'])
												<div class="marca-dagua"><span>RASCUNHO</span></div>
											@elseif($dados['status'] === 'cancelada' || !empty($dados['eh_cancelada']))
												<div class="marca-dagua"><span>CANCELADA</span></div>
											@endif

											<table class="info-nota-eletronica">
												<tbody>
													<tr class="left">
														<td class=" logo">
															<img src="{{ asset('images/logo-brasao-prefeitura.png') }}"
																alt="Logo Prefeitura Municipal" class="logo">
														</td>
														<td colspan="4">
															<h2 id="nomeMunicipio">Município de Cacoal</h2>
															<h4 id="nomeSecretaria" style="margin-bottom: 0;">Secretaria
																Municipal de Fazenda</h4>
															<address id="enderecoPrefeitura">Departamento de
																Fiscalização Tributária - Rua Anísio Serrão, n° 2.100 -
																Centro - CEP 76.963-804 - Cacoal/RO - Brasil - Fone:
																(69) 3907-4131</address>
														</td>
														<td rowspan="4" id="tdQrcode"
															style="text-align: center; vertical-align: top;">
															@if(!$dados['eh_rascunho'])
																<div class="fundo-selo">
																	<div style="text-align: center;">
																		<div style="font-weight: bold;">
																			Nota:
																			{{ \Str::limit($dados['numero_nfse'], 6, '') }}
																		</div>
																		<div
																			style="font-weight: bold; font-size: 14pt; margin-top: 4px; margin-bottom: 4px;">
																			{{ substr($dados['numero_nfse'], 6) }}
																		</div>
																		<div style="font-weight: bold; font-size: 8pt;">
																			Código Verificação
																		</div>
																		<div
																			style="font-size: 11pt; margin-top: 4px; margin-bottom: 4px;">
																			{{ $dados['codigo_verificacao'] }}
																		</div>
																	</div>
																	<div class="qrcode">
																		@if(!empty($dados['qrcode_base64']) || !empty($dados['url_qrcode']))
																			<div>
																				<img src="{{ $dados['qrcode_base64'] }}">
																			</div>
																			<span class="descricao-qrcode"
																				style="display: block; font-size: 7px; line-height: 1.15; margin-top: 4px; text-align: center; font-family: Verdana, sans-serif;">
																				A autenticidade desta NFS-e pode ser verificada
																				pela
																				leitura deste código QR ou pela consulta da
																				chave de
																				acesso no portal nacional da NFS-e.
																			</span>
																		@endif
																	</div>
																</div>
															@endif
														</td>
													</tr>
													<tr>
														<td colspan="5">
															<hr>
															<h1>{{ $dados['titulo_nota'] }}</h1>
															<span id="rps"><i>{{ $dados['rps_texto'] }}</i></span>
														</td>
													</tr>
													<tr>
														<td colspan="5" class="left">
															<table style="width: 100%;">
																<tbody>
																	<tr class="left cabecalho">
																		<td>
																			<small>Emissão (Horário de Brasília)</small>
																			<br>
																			<span
																				class="negrito">{{$dados['data_emissao'] }}</span>
																		</td>
																		<td>
																			<small>Período de Competência</small>
																			<br>
																			<span
																				class="negrito">{{$dados['competencia'] }}</span>
																		</td>
																		<td>
																			<small>Município de Prestação do
																				Serviço</small>
																			<br>
																			<span
																				class="negrito">{{$dados['municipio_prestacao'] }}</span>
																		</td>
																	</tr>
																	<tr class="left">
																		<td>
																			<small>Reg. Especial Tributação</small>
																			<br>
																			<span class="negrito">
																				{{ $dados['regime_especial_tributacao']	}}
																			</span>
																		</td>
																		<td>
																			<small>Exigibilidade do ISS</small>
																			<br>
																			<span class="negrito">
																				{{ $dados['exigibilidade_iss'] }}
																				</span>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>
													<tr>
														<td colspan="5">
															<hr>
														</td>
													</tr>

													<!-- Dados do Prestador -->
													<tr>
														<td colspan="6">
															<h2>Prestador de Serviços</h2>
														</td>
													</tr>

													<tr>
														<td colspan="6">
															<table style="width: 100%;">
																<tbody>
																	<tr class="left">
																		<td colspan="6">
																			<small>Razão Social</small>
																			<br>
																			<span
																				class="negrito">{{$dados['prestador_razao_social']}}</span>
																		</td>
																	</tr>
																	<tr class="left cabecalho">
																		<td colspan="4">
																			<small>Nome Fantasia</small>
																		</td>
																		<td colspan="2">
																			<small>Email</small>
																		</td>
																	</tr>
																	<tr class="left">
																		<td colspan="4">
																			<span
																				class="negrito">{{$dados['prestador_nome_fantasia']}}</span>
																		</td>
																		<td colspan="2">
																			<span
																				class="negrito">{{$dados['prestador_email']
																				}}</span>
																		</td>
																	</tr>
																	<tr class="left cabecalho">
																		<td>
																			<small>CPF/CNPJ</small>
																		</td>
																		<td>
																			<small>Inscrição Municipal</small>
																		</td>
																		<td>
																			<small>Inscrição Estadual</small>
																		</td>
																		<td>
																			<small>Simples Nacional</small>
																		</td>
																		<td>
																			<small>Incentivador Cultural</small>
																		</td>
																		<td>
																			<small>Fone/Fax</small>
																		</td>
																	</tr>
																	<tr class="left">
																		<td>
																			<span id="documento-prestador"
																				class="negrito">{{$dados['prestador_cnpj']
																				}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['prestador_inscricao_municipal']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['prestador_inscricao_estadual']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['prestador_simples_nacional']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['prestador_incentivador_cultural']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['prestador_telefone']
																				}}</span>
																		</td>
																	</tr>

																	<tr class="left cabecalho">
																		<td colspan="6">
																			<small>Endereço</small>
																			<br>
																			<address class="italico negrito">
																				{{ $dados['prestador_endereco'] }}
																				</address>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>

													<!-- Dados do Tomador -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>TOMADOR DE SERVIÇOS</h2>
														</td>
													</tr>
													<tr>
														<td colspan="6">
															<table style="width: 100%;">
																<tbody>
																	<tr class="left cabecalho">
																		<td colspan="7">
																			<small>Nome/Razão Social</small>
																		</td>
																	</tr>
																	<tr class="left">
																		<td colspan="7">
																			<div class="break-text">
																				<span class=" negrito">{{
																					$dados['tomador_nome'] }}</span>
																			</div>
																		</td>
																	</tr>

																	<tr class="left cabecalho">
																		<td>
																			<small>CPF/CNPJ</small>
																			<br>
																			<span
																				class="negrito">{{$dados['tomador_cpf_cnpj']
																				}}</span>
																		</td>
																		<td>
																			<small>Inscrição Municipal</small>
																			<br>
																			<span
																				class="negrito">{{$dados['tomador_inscricao_municipal']}}</span>
																		</td>
																		<td>
																			<small>Inscrição Estadual</small>
																			<br>
																			<span
																				class="negrito">{{$dados['tomador_inscricao_estadual']}}</span>
																		</td>
																		<td>
																			<small>Fone/Fax</small>
																			<br>
																			<span
																				class="negrito">{{$dados['tomador_telefone']
																				}}</span>
																		</td>
																		<td>
																			<small>E-mail</small>
																			<br>
																			<span
																				class="negrito">{{$dados['tomador_email']
																				}}</span>
																		</td>
																	</tr>

																	<tr class="left cabecalho">
																		<td colspan="5">
																			<small>Endereço</small>
																			<br>
																			<address class="italico negrito">
																				{{ $dados['tomador_endereco'] }}
																				</address>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>

													<!-- Serviço Prestado -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>Serviço Prestado</h2>
															<p class="letras-pequenas negrito" id="serico-prestado">
																{{ $dados['servico_prestado_texto'] }}
																</p>
														</td>
													</tr>

													<!-- Descrição dos Serviços -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>DESCRIÇÃO DOS SERVIÇOS</h2>
														</td>
													</tr>
													<tr class="left">
														<td colspan="6">
															<div style="position:relative;">
																<p id="descricao-nota-eletronica">
																	{!! $dados['discriminacao_servicos'] !!}
																	</p>
																@if($dados['eh_cancelada'] ||
																	!empty($dados['data_cancelamento']))
																				<p class="dados-cancelamento">
																			<span class="negrito">Data do
																				Cancelamento:</span>
																			{{ $dados['data_cancelamento'] }}
																			<br>
																			<span class="negrito">MOTIVO:</span>
																			{{ $dados['motivo_cancelamento'] }}
																			<br>
																			@if(!empty($dados['justificativa_cancelamento']))
																					<span class="negrito">Justificativa: </span>
																				{{ $dados['justificativa_cancelamento'] }}
																				<br>
																			@endif
																			</p>
																@endif </div>
														</td>
													</tr>

													<!-- Tributos Federais -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>TRIBUTOS FEDERAIS</h2>
														</td>
													</tr>
													<tr class="right">
														<td colspan="6">
															<table style="width: 100%;">
																<tbody>
																	<tr class="cabecalho">
																		<td>
																			<small>INSS (R$)</small>
																			<br>
																			<span class="negrito">{{$dados['valor_inss']
																				}}</span>
																		</td>
																		<td>
																			<small>IR (R$)</small>
																			<br>
																			<span class="negrito">{{
																				$dados['valor_ir']}}</span>
																		</td>
																		<td>
																			<small>PIS (R$)</small>
																			<br>
																			<span class="negrito">{{
																				$dados['valor_pis']}}</span>
																		</td>
																		<td>
																			<small>COFINS (R$)</small>
																			<br>
																			<span
																				class="negrito">{{$dados['valor_cofins']}}</span>
																		</td>
																		<td>
																			<small>CSLL (R$)</small>
																			<br>
																			<span class="negrito">{{$dados['valor_csll']
																				}}</span>
																		</td>
																		<td>
																			<small>Outras Retenções (R$)</small>
																			<br>
																			<span
																				class="negrito">{{$dados['outras_retencoes']}}</span>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>

													<!-- Valores -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>VALORES</h2>
														</td>
													</tr>
													<tr class="right">
														<td colspan="6">
															<table style="width: 100%;">
																<tbody>
																	<tr class="cabecalho">
																		<td>
																			<small>Deduções (R$)</small>
																		</td>
																		<td>
																			<small>Desc. Cond. (R$)</small>
																		</td>
																		<td>
																			<small>Desc. Incond. (R$)</small>
																		</td>
																		<td>
																			<small>Base de Cálculo ISS (R$)</small>
																		</td>
																		<td>
																			<small>Alíquota ISS (%)</small>
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<span
																				class="negrito">{{$dados['valor_deducoes']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['desconto_condicionado']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['desconto_incondicionado']
																				}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['base_calculo_iss']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['aliquota_iss']}}</span>
																		</td>
																	</tr>

																	<tr class="cabecalho">
																		<td>
																			<small>Valor dos Serviços (R$)</small>
																		</td>
																		<td>
																			<small>ISS (R$)</small>
																		</td>
																		<td>
																			<small>ISS Retido (R$)</small>
																		</td>
																		<td>
																			<small>Valor Líquido (R$)</small>
																		</td>
																		<td>
																			<small class="negrito valor-total">
																				Valor Total da Nota (R$)
																			</small>
																		</td>
																	</tr>
																	<tr>
																		<td>
																			<span
																				class="negrito">{{$dados['valor_servicos']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{ $dados['valor_iss']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['iss_retido'] }}</span>
																		</td>
																		<td>
																			<span
																				class="negrito">{{$dados['valor_liquido']}}</span>
																		</td>
																		<td>
																			<span
																				class="negrito valor-total"><mark>{{$dados['valor_total'] }}</mark></span>
																		</td>
																	</tr>
																</tbody>
															</table>
														</td>
													</tr>

													<!-- Outras Informações -->
													<tr>
														<td colspan="6">
															<hr>
															<h2>OUTRAS INFORMAÇÕES</h2>
															<div id="outras-informacoes">
																{!! $dados['outras_informacoes'] !!}
															</div>
														</td>
													</tr>
													<tr class="break">
														<td colspan="6" class="letras-pequenas">
															<hr>
															<span>Visualizado em: {{ $dados['visualizado_em'] }}</span>
															| Para validação desta NFSe acesse: <span
																class=" text-info">{{ $dados['url_validacao'] }}</span>
															<br>
															Esta NFS-e é autodeclaratória. Esta NFS-e foi emitida com
															respaldo no Decreto nº 4.749 de 06 de fevereiro de 2013.
														</td>
													</tr>
												</tbody>
											</table>
											<div style="clear:both;"></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>

</html>