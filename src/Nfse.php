<?php

declare(strict_types=1);

namespace Larafocus;

use Larafocus\Infrastructure\FocusResponse;
use Larafocus\Infrastructure\Http;
use Larafocus\Nfse\DTO\NfseRequest;

readonly class Nfse
{
    public function __construct(private Http $http) {}

    /**
     * @param  NfseRequest|array<string, mixed>  $parameters
     *
     * @phpstan-param NfseRequest|array{data_emissao: string, natureza_operacao: string, optante_simples_nacional: bool, prestador: array{cnpj: string, inscricao_municipal: string, codigo_municipio?: string|null}, tomador: array{cpf?: string|null, cnpj?: string|null, razao_social?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, inscricao_municipal?: string|null, email?: string|null, telefone?: string|null, endereco?: array{logradouro?: string|null, tipo_logradouro?: string|null, numero?: string|null, complemento?: string|null, bairro?: string|null, codigo_municipio?: string|null, uf?: string|null, cep?: string|null}|null}, servico: array{valor_servicos: float, iss_retido: bool, item_lista_servico: string, discriminacao: string, codigo_municipio: string, aliquota?: float|null, valor_deducoes?: float|null, valor_pis?: float|null, valor_cofins?: float|null, valor_inss?: float|null, valor_ir?: float|null, valor_csll?: float|null, valor_iss?: float|null, valor_iss_retido?: float|null, outras_retencoes?: float|null, base_calculo?: float|null, desconto_incondicionado?: float|null, desconto_condicionado?: float|null, codigo_cnae?: string|null, codigo_tributario_municipio?: string|null, codigo_nbs?: string|null, codigo_indicador_operacao?: string|null, ibs_cbs_classificacao_tributaria?: string|null, ibs_cbs_situacao_tributaria?: string|null, ibs_cbs_base_calculo?: float|null, ibs_uf_aliquota?: float|null, ibs_mun_aliquota?: float|null, cbs_aliquota?: float|null, ibs_uf_valor?: float|null, ibs_mun_valor?: float|null, cbs_valor?: float|null, percentual_total_tributos?: float|null, fonte_total_tributos?: string|null}, regime_especial_tributacao?: string|null, incentivador_cultural?: bool|null, intermediario?: array{cpf?: string|null, cnpj?: string|null, nif?: string|null, motivo_ausencia_nif?: string|null, razao_social?: string|null, inscricao_municipal?: string|null}|null, codigo_obra?: string|null, art?: string|null, numero_nfse_substituido?: string|null, numero_rps_substituido?: string|null, serie_rps_substituido?: string|null, tipo_rps_substituido?: string|null} $parameters
     */
    public function create(string $reference, NfseRequest|array $parameters): FocusResponse
    {
        if (is_array($parameters)) {
            $parameters = NfseRequest::fromArray($parameters);
        }

        return $this->http->post('/nfse?ref='.urlencode($reference), $parameters->toArray());
    }

    public function get(string $reference): FocusResponse
    {
        return $this->http->get('/nfse/'.urlencode($reference));
    }

    /** @param array<string, mixed> $parameters */
    public function cancel(string $reference, array $parameters = []): FocusResponse
    {
        return $this->http->delete('/nfse/'.urlencode($reference), $parameters);
    }

    /** @param array<string, mixed> $parameters */
    public function email(string $reference, array $parameters = []): FocusResponse
    {
        return $this->http->post('/nfse/'.urlencode($reference).'/email', $parameters);
    }

    public function hook(string $reference): FocusResponse
    {
        return $this->http->post('/nfse/'.urlencode($reference).'/hook');
    }
}
