<?php
declare(strict_types = 1);
namespace GPRS\System;

use BCL\System\AbstractObject;

/**
 * Gerenciador de monitores.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System
 */
final class MonitorManager extends AbstractObject
{

    /**
     * Instância do gerenciador de modems.
     *
     * @var ModemManagerInterface
     */
    private $modems;

    /**
     * Instância do gerenciador de registros.
     *
     * @var LogManager
     */
    private $logger;

    /**
     * Instância do gerenciador de conexões.
     *
     * @var ConnectionManager
     */
    private $connections;

    /**
     * Lista de monitores.
     *
     * @var array
     */
    private $monitors;

    /**
     * Construtor.
     *
     * @param ModemManagerInterface $modems
     *            Instância do gerenciador de modems.
     * @param LogManager $logger
     *            Instância do gerenciador de registros.
     * @param ConnectionManager $connections
     *            Instância do gerenciador de conexões.
     */
    public function __construct(ModemManagerInterface $modems, LogManager $logger, ConnectionManager $connections)
    {
        $this->modems = $modems;
        $this->logger = $logger;
        $this->connections = $connections;
        $this->monitors = [];
    }

    /**
     * Adiciona um monitor à lista de monitores.
     *
     * @param $className $monitor
     *            Classe do monitor.
     * @throws \Exception
     * @return void
     */
    public function register(string $className)
    {
        $monitor = __NAMESPACE__ . "\\Monitor\\" . $className;

        if (isset($this->monitors[$className])) {

            $message = sprintf('O monitor \'%s\' já foi adicionado anteriormente.', $className);
            throw new \Exception($message);
        }

        $this->monitors[$className] = new $monitor($this->modems, $this->logger, $this->connections);
    }

    /**
     * Percorre a lista dos monitores executando o método de monitoramento de cada monitor.
     *
     * @return void
     */
    public function monitore()
    {
        foreach ($this->monitors as $monitor) {
            $monitor->monitore();
        }
    }
}