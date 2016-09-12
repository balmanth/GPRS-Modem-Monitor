<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor;

use BCL\System\Actions\AbstractActionListener;

/**
 * Gerenciador de ações etapas de monitoramento.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor
 */
final class MonitorStageListener extends AbstractActionListener
{

    /**
     * Executa a ação relacionada a uma etapa de monitoramento.
     *
     * @param MonitorStageAction $action
     *            Instância da ação executada.
     */
    public function execute(MonitorStageAction $action)
    {
        $this->dispatch($action);
    }
}