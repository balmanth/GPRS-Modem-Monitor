# GPRS Modem Monitor

[![PHP 7.0](https://img.shields.io/badge/PHP-%3E%3D%207.0-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/balmanth/GPRS-Modem-Monitor/?branch=develop)

Serviço para leitura das atividades registradas por modems celulares utilizados em monitoramento e automação.

## Equipamentos
* **Modem e Datalogger ABS**
  + Geral
    * Módulo para leitura de informações do hardware.
    * Módulo para limpeza dos valores acumulado nos totalizadores.
  + Relógio Interno
    * Módulo para leitura de data e hora.
    * Módulo para sincronizar data e hora.
  + Sinal.
    * Módulo para leitura nível de sinal (dBm).
  + Canais
    * Módulo para leitura da configuração dos canais.
  + Memória
    * Módulo para leitura das informações sobre a memória (tamanho do bloco, quantidade de blocos e índice do próximo bloco a se gravado)
    * Módulo para leitura dos dados armazenadas na memória.

## Requisitos
+ PHP 7.0
+ [BCL](https://github.com/balmanth/BCL)
