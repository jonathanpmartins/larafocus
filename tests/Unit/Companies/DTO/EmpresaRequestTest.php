<?php

use Larafocus\Companies\DTO\EmpresaRequest;
use Larafocus\Companies\DTO\Enums\OrientacaoDanfe;
use Larafocus\Companies\DTO\Enums\RegimeTributario;
use Larafocus\Companies\DTO\Enums\SmtpAutenticacao;
use Larafocus\Companies\DTO\Enums\SmtpVerificacaoOpenssl;

covers(EmpresaRequest::class);

test('constructs with no fields', function () {
    $request = new EmpresaRequest;

    expect($request->nome)->toBeNull()
        ->and($request->cnpj)->toBeNull()
        ->and($request->regime_tributario)->toBeNull();
});

test('constructs with basic fields', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste Ltda',
        cnpj: '12345678000195',
        regime_tributario: RegimeTributario::SimplesNacional,
    );

    expect($request->nome)->toBe('Empresa Teste Ltda')
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->regime_tributario)->toBe(RegimeTributario::SimplesNacional);
});

test('constructs with all enum fields', function () {
    $request = new EmpresaRequest(
        regime_tributario: RegimeTributario::RegimeNormal,
        orientacao_danfe: OrientacaoDanfe::Landscape,
        smtp_autenticacao: SmtpAutenticacao::Login,
        smtp_modo_verificacao_openssl: SmtpVerificacaoOpenssl::Peer,
    );

    expect($request->regime_tributario)->toBe(RegimeTributario::RegimeNormal)
        ->and($request->orientacao_danfe)->toBe(OrientacaoDanfe::Landscape)
        ->and($request->smtp_autenticacao)->toBe(SmtpAutenticacao::Login)
        ->and($request->smtp_modo_verificacao_openssl)->toBe(SmtpVerificacaoOpenssl::Peer);
});

test('toArray omits null values', function () {
    $request = new EmpresaRequest(
        nome: 'Empresa Teste',
        habilita_nfe: true,
    );

    expect($request->toArray())->toBe([
        'nome' => 'Empresa Teste',
        'habilita_nfe' => true,
    ]);
});

test('toArray converts enums to values', function () {
    $request = new EmpresaRequest(
        regime_tributario: RegimeTributario::SimplesNacional,
        orientacao_danfe: OrientacaoDanfe::Portrait,
        smtp_autenticacao: SmtpAutenticacao::CramMd5,
        smtp_modo_verificacao_openssl: SmtpVerificacaoOpenssl::None,
    );

    $array = $request->toArray();

    expect($array['regime_tributario'])->toBe(1)
        ->and($array['orientacao_danfe'])->toBe('portrait')
        ->and($array['smtp_autenticacao'])->toBe('cram_md5')
        ->and($array['smtp_modo_verificacao_openssl'])->toBe('none');
});

test('toArray includes boolean false values', function () {
    $request = new EmpresaRequest(
        habilita_nfe: false,
        habilita_nfce: false,
    );

    expect($request->toArray())->toBe([
        'habilita_nfe' => false,
        'habilita_nfce' => false,
    ]);
});

test('toArray includes integer zero values', function () {
    $request = new EmpresaRequest(
        numero: 0,
        cep: 0,
    );

    expect($request->toArray())->toBe([
        'numero' => 0,
        'cep' => 0,
    ]);
});

test('fromArray creates instance with basic fields', function () {
    $request = EmpresaRequest::fromArray([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000195',
    ]);

    expect($request->nome)->toBe('Empresa Teste')
        ->and($request->cnpj)->toBe('12345678000195')
        ->and($request->cpf)->toBeNull();
});

test('fromArray converts enum values', function () {
    $request = EmpresaRequest::fromArray([
        'regime_tributario' => 3,
        'orientacao_danfe' => 'landscape',
        'smtp_autenticacao' => 'login',
        'smtp_modo_verificacao_openssl' => 'peer',
    ]);

    expect($request->regime_tributario)->toBe(RegimeTributario::RegimeNormal)
        ->and($request->orientacao_danfe)->toBe(OrientacaoDanfe::Landscape)
        ->and($request->smtp_autenticacao)->toBe(SmtpAutenticacao::Login)
        ->and($request->smtp_modo_verificacao_openssl)->toBe(SmtpVerificacaoOpenssl::Peer);
});

test('fromArray handles missing optional fields', function () {
    $request = EmpresaRequest::fromArray([]);

    expect($request->nome)->toBeNull()
        ->and($request->regime_tributario)->toBeNull()
        ->and($request->habilita_nfe)->toBeNull();
});

test('round trip fromArray toArray preserves data for all fields', function () {
    $data = [
        // Dados basicos
        'nome' => 'Empresa Teste Ltda',
        'nome_fantasia' => 'Empresa Teste',
        'cnpj' => '12345678000195',
        'cpf' => '12345678901',
        'inscricao_estadual' => 123456,
        'inscricao_municipal' => 654321,
        'regime_tributario' => 1,

        // Endereco
        'logradouro' => 'Rua Teste',
        'numero' => 100,
        'complemento' => 'Sala 1',
        'bairro' => 'Centro',
        'municipio' => 'Sao Paulo',
        'cep' => 12345678,
        'uf' => 'SP',

        // Contato
        'telefone' => '1199998888',
        'email' => 'contato@empresa.com',

        // Habilitacoes de documentos
        'habilita_nfe' => true,
        'habilita_nfce' => true,
        'habilita_nfse' => true,
        'habilita_nfsen_producao' => true,
        'habilita_nfsen_homologacao' => true,
        'habilita_cte' => true,
        'habilita_mdfe' => true,
        'habilita_nfcom' => true,
        'habilita_manifestacao' => true,
        'habilita_manifestacao_cte' => true,
        'habilita_nfsen_recebidas_producao' => true,
        'habilita_nfsen_recebidas_homologacao' => true,

        // Comunicacao
        'enviar_email_destinatario' => true,
        'enviar_email_homologacao' => true,
        'discrimina_impostos' => true,

        // NFCe
        'habilita_contingencia_offline_nfce' => true,
        'reaproveita_numero_nfce_contingencia' => true,
        'csc_nfce_producao' => 'CSC-PROD-123',
        'id_token_nfce_producao' => 1,
        'csc_nfce_homologacao' => 'CSC-HOM-456',
        'id_token_nfce_homologacao' => 2,

        // DANFe
        'orientacao_danfe' => 'portrait',
        'recibo_danfe' => true,
        'exibe_sempre_ipi_danfe' => true,
        'exibe_issqn_danfe' => true,
        'exibe_impostos_adicionais_danfe' => true,
        'exibe_rastro_danfe' => true,
        'exibe_unidade_tributaria_danfe' => true,
        'exibe_sempre_volumes_danfe' => true,
        'exibe_composicao_carga_mdfe' => true,
        'mostrar_danfse_badge' => true,

        // Numeracao de documentos
        'proximo_numero_nfe_producao' => '1000',
        'proximo_numero_nfe_homologacao' => '2000',
        'serie_nfe_producao' => '1',
        'serie_nfe_homologacao' => '2',
        'proximo_numero_nfce_producao' => '3000',
        'proximo_numero_nfce_homologacao' => '4000',
        'serie_nfce_producao' => '3',
        'serie_nfce_homologacao' => '4',
        'proximo_numero_nfse_producao' => '5000',
        'proximo_numero_nfse_homologacao' => '6000',
        'serie_nfse_producao' => '5',
        'serie_nfse_homologacao' => '6',
        'proximo_numero_nfsen_producao' => '7000',
        'proximo_numero_nfsen_homologacao' => '8000',
        'serie_nfsen_producao' => '7',
        'serie_nfsen_homologacao' => '8',
        'proximo_numero_cte_producao' => '9000',
        'proximo_numero_cte_homologacao' => '10000',
        'serie_cte_producao' => '9',
        'serie_cte_homologacao' => '10',
        'proximo_numero_cte_os_producao' => '11000',
        'proximo_numero_cte_os_homologacao' => '12000',
        'serie_cte_os_producao' => '11',
        'serie_cte_os_homologacao' => '12',
        'proximo_numero_mdfe_producao' => '13000',
        'proximo_numero_mdfe_homologacao' => '14000',
        'serie_mdfe_producao' => '13',
        'serie_mdfe_homologacao' => '14',
        'proximo_numero_nfcom_producao' => '15000',
        'proximo_numero_nfcom_homologacao' => '16000',
        'serie_nfcom_producao' => '15',
        'serie_nfcom_homologacao' => '16',

        // Certificado digital
        'arquivo_certificado_base64' => 'base64cert==',
        'senha_certificado' => 'senha123',

        // Logo
        'arquivo_logo_base64' => 'base64logo==',
        'delete_logo' => true,

        // Responsavel
        'nome_responsavel' => 'Joao Silva',
        'cpf_responsavel' => '98765432100',
        'login_responsavel' => 'joao.silva',
        'senha_responsavel' => 'senhaResp123',
        'senha_responsavel_preenchida' => true,

        // Contabilidade
        'cpf_cnpj_contabilidade' => '11222333000144',
        'data_inicio_recebimento_nfe' => '2024-01-01',
        'data_inicio_recebimento_cte' => '2024-02-01',

        // SMTP
        'smtp_endereco' => 'smtp.empresa.com',
        'smtp_dominio' => 'empresa.com',
        'smtp_porta' => 587,
        'smtp_autenticacao' => 'plain',
        'smtp_login' => 'smtp@empresa.com',
        'smtp_senha' => 'smtpSenha123',
        'smtp_remetente' => 'noreply@empresa.com',
        'smtp_responder_para' => 'contato@empresa.com',
        'smtp_modo_verificacao_openssl' => 'peer',
        'smtp_habilita_starttls' => true,
        'smtp_ssl' => true,
        'smtp_tls' => true,

        // Processamento sincrono
        'nfe_sincrono' => true,
        'nfe_sincrono_homologacao' => true,
        'mdfe_sincrono' => true,
        'mdfe_sincrono_homologacao' => true,
    ];

    $result = EmpresaRequest::fromArray($data)->toArray();

    expect($result)->toBe($data);
});
