# GPRS Modem Monitor

[![PHP 7.0](https://img.shields.io/badge/PHP-%3E%3D%207.0-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/?branch=develop)

Serviço para leitura de atividades registradas por um modem celular utilizado em monitoramento e/ou automação.

## Características
* Protocolo de comunicação Modbus.
* Multiplas conexões em modo assíncrono.
* Comunicação com vários equipamentos em paralelo.
* [Log das atividades do serviço.](#log-de-atividade)
* [Utilize banco de dados ou API.](#banco-de-dados-ou-api)
* [Extensão de funcionalidades com hooks.](#hooks)

## Log de atividade
Todas as atividades principais do serviço geram mensagens de log, através de manipuladores é possível determinar qual será a ação tomada com as informações da atividade executada.

+ **Manipuladores**
	* EchoLogger, Envia toda informação da atividade executada para STDOUT.

## Banco de dados ou API
Suporte para carregamento de informações do banco de dados ou API.

+ **Tipos de carregamento**
	* Informações para conexão com os equipamentos.
	* Informações para conversão de valores brutos (lidos da memória do equipamento).

## Hooks
Utilize hooks para estender funcionalidades ou executar ações em resposta aos eventos do equipamento.
Todas as notificações são enviadas após processamento de uma mensagem de resposta do equipamento.
É de responsabilidade dos módulos de ação enviar quaisquer notificações, se uma ação estiver desativada suas respectivas notificações não serão processadas.

+ **Notificações**
	* Informações do hardware.
    * Limpeza dos valores nos totalizadores.
    * Data e hora.
    * Sincronização de data e hora.
    * Nível de sinal.
    * Configuração dos canais.
    * Informações sobre a memória.
    * Valores da memória do equipamento.
    
## Equipamentos
* **Modems e Dataloggers ABS**
	+ Ações
		* Obtér informações sobre o hardware.
		* Obtér a data e hora do relógio interno.
		* Ler quais canais estão habilitados.
		* Obtér informações sobre a memória.
		* Ler dados armazenadas na memória a partir de um índice.
		* Limpar os valores dos totalizadores.
		* Sincronizar a data e hora do relógio interno.
* **Modems ABS**
	+ Ações
		* Obtér nível de sinal.

## Requisitos
+ PHP 7.0
+ [BCL](https://github.com/balmanth/BCL)
