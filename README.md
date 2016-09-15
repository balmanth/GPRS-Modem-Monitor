# GPRS Modem Monitor

[![PHP 7.0](https://img.shields.io/badge/PHP-%3E%3D%207.0-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/?branch=develop)

Serviço para leitura de atividades registradas por um modem celular utilizado em monitoramento e/ou automação.

## Características
* Protocolo de comunicação Modbus.
* Multiplas conexões em modo assíncrono.
* Comunicação com vários equipamentos em paralelo.
* Log das atividades do serviço.
* Utilize banco de dados ou sua API.
* Extensão de funcionalidades com hooks.

## Log das atividades
Todas as atividades principais do serviço geram mensagens de log, através de manipuladores é possível determinar qual será a ação tomada com as informações da atividade executada.

+ **Manipuladores**
	* EchoLogger, Envia toda informação da atividade executada para STDOUT.

## Banco de Dados ou API
Suporte para carregamento de informações do banco de dados ou API.
+ Tipos de carregamento:
	* Informações para conexão com os equipamentos.
	* Informações para conversão de valores brutos (lidos da memória do equipamento).

## Hooks
Utilize hooks para estender as funcionalidades, criar relatórios personalizados ou executar ações em resposta aos eventos do equipamento.
Todas as notificações são enviadas após processamento de uma mensagem de resposta do equipamento.
É de responsabilidade do módulo enviar as notificações, logo, se um módulo estiver desativado suas respectivas notificações não serão processadas.

+ Notificações dos módulos para equipamentos ABS:
	* Informações do hardware.
    * Limpeza dos valores nos totalizadores.
    * Data e hora.
    * Sincronização de data e hora.
    * Nível de sinal.
    * Configuração dos canais.
    * Informações sobre a memória.
    * Valores da memória do equipamento.
    
## Equipamentos
* **Modem e Datalogger ABS**
  + Geral
    * Módulo para leitura das informações sobre o hardware.
    * Módulo para limpeza dos valores nos totalizadores.
  + Relógio
    * Módulo para leitura de data e hora.
    * Módulo para sincronizar data e hora.
  + Sinal
    * Módulo para leitura nível de sinal (dBm).
  + Canais
    * Módulo para leitura da configuração dos canais.
  + Memória
    * Módulo para leitura das informações sobre a memória.
    * Módulo para leitura dos dados armazenadas na memória.

## Requisitos
+ PHP 7.0
+ [BCL](https://github.com/balmanth/BCL)
