<?php
declare(strict_types = 1);
namespace GPRS\System\Monitor\ABS;

use BCL\System\AbstractObject;

/**
 * Contêm os métodos necessários para montar e desmontar mensagens no protocolo Modbus para modems ABS/ALR.
 *
 * @since 1.0
 * @version 1.0
 * @author Silas B. Domingos
 * @copyright Silas B. Domingos
 * @package GPRS\System\Monitor\ABS
 */
final class ABSModbusProtocol extends AbstractObject
{

    /**
     * Calcula o CRC da mensagem.
     *
     * @param string $message
     *            Mensagem Modbus.
     * @param int $ignoredLen
     *            Bytes ignorados no final da mensagem.
     * @return array Parte maior e menor do CRC.
     */
    private function calculeCRC(string $message, int $ignoredLen = 0): array
    {
        $msgLength = strlen($message);
        $totalLength = ($msgLength - $ignoredLen);

        $crcHi = 0x00FF;
        $crcLo = 0x00FF;

        for ($i = 0; $i < $totalLength; $i ++) {

            $crcLo = ($crcLo ^ ord($message[$i])) & 0x00FF;

            for ($j = 0; $j < 8; $j ++) {

                $byteAux = ($crcLo & 0x0001);

                $crcLo = (($crcLo >> 1) | ($crcHi & 0x0001) << 7);
                $crcHi = ($crcHi >> 1);

                if ($byteAux === 1) {
                    $crcHi = ($crcHi ^ 0x00A0);
                    $crcLo = ($crcLo ^ 0x0001);
                }
            }
        }

        return [
            ($crcLo & 0x00FF),
            ($crcHi & 0x00FF)
        ];
    }

    /**
     * Valida o CRC da mensagem.
     *
     * @param string $message
     *            Mensagem Modbus com assinatura CRC nos últimos 2 bytes.
     * @return void
     * @throws \Exception
     */
    private function validateCRC(string &$message)
    {
        $msgLength = strlen($message);
        $crcValue = $this->calculeCRC($message, 2);

        if (ord($message[($msgLength - 2)]) !== $crcValue[0] || ord($message[($msgLength - 1)]) !== $crcValue[1]) {
            throw new \Exception('CRC da mensagem inválido, mensagem corrompida.');
        }
    }

    /**
     * Testa se uma mensagem Modbus é válida e corresponde aos critérios informados nos parâmetros.
     *
     * @param string $message
     *            Mensagem Modbus
     * @param int $address
     *            Endereço de rede esperado.
     * @param int $func
     *            Código da função esperada.
     * @return void
     * @throws \Exception
     */
    private function validateMessage(string $message, int $address, int $func)
    {
        if (($msgLength = strlen($message)) < 6) {
            $message = sprintf('O comprimento da mensagem (%d) é muito curto (mínimo 6 bytes).', $msgLength);
            throw new \Exception($message);
        }

        if (($msgAddress = (ord($message[0]) & 0x00FF)) !== $address) {
            $message = sprintf('O endereço de rede esperado é \'%d\', \'%d\' foi recebido.', $address, $msgAddress);
            throw new \Exception($message);
        }

        if (($msgFunc = ord($message[1])) !== $func) {
            $message = sprintf('O função Modbus esperada é \'%d\', \'%d\' foi recebido.', $func, $msgFunc);
            throw new \Exception($message);
        }
    }

    /**
     * Monta o intervalo de registros Modbus do cabeçalho da mensagem.
     *
     * @param int $startReg
     *            Registro inicial.
     * @param int $totalRegs
     *            Número total de registros.
     * @return string Intervalo de registros para uma mensagem Modbus.
     * @throws \Exception
     */
    private function packHeaderRange(int $startReg, int $totalRegs): string
    {
        if ($totalRegs > 125) {
            throw new Exception('O número de registros deve ser igual ou inferior a 125.');
        }

        return pack('C*', ($startReg >> 8), ($startReg & 0x00FF), 0, $totalRegs);
    }

    /**
     * Monta o cabeçalho da mensagem.
     *
     * @param int $address
     *            Endereço da rede.
     * @param int $func
     *            Código da função.
     * @param int $startReg
     *            Registro inicial.
     * @param int $totalRegs
     *            Número total de registros.
     * @return string Cabeçalho para uma mensagem Modbus.
     * @throws \Exception
     */
    private function packHeader(int $address, int $func, int $startReg, int $totalRegs): string
    {
        return pack('C*', $address, $func) . $this->packHeaderRange($startReg, $totalRegs);
    }

    /**
     * Monta o conteúdo da mensagem.
     *
     * @param int ...$regs
     *            Dados para escrita.
     * @return string Corpo de uma mensagem Modbus.
     */
    private function packContent(int ...$regs): string
    {
        return pack('Cn*', (count($regs) * 2), ...$regs);
    }

    /**
     * Calcula o CRC da mensagem.
     *
     * @param string $message
     *            Mensagem para calculo do CRC.
     * @return string Corpo da mensagem Modbus.
     */
    private function packCRC(string $message): string
    {
        return pack('C*', ...$this->calculeCRC($message));
    }

    /**
     * Prepara uma mensagem que utiliza a função Modbus 3.
     *
     * @param int $address
     *            Endereço da rede.
     * @param int $startReg
     *            Registro inicial da leitura.
     * @param int $totalRegs
     *            Número total de registros para leitura.
     * @return string Mensagem Modbus.
     * @throws \Exception
     */
    public function pack3(int $address, int $startReg, int $totalRegs): string
    {
        $message = $this->packHeader($address, 3, $startReg, $totalRegs);
        return $message . $this->packCRC($message);
    }

    /**
     * Prepara uma mensagem que utiliza a função Modbus 4.
     *
     * @param int $address
     *            Endereço da rede.
     * @param int $startReg
     *            Registro inicial da leitura.
     * @param int $totalRegs
     *            Número total de registros para leitura.
     * @return string Mensagem Modbus.
     * @throws \Exception
     */
    public function pack4(int $address, int $startReg, int $totalRegs): string
    {
        $message = $this->packHeader($address, 4, $startReg, $totalRegs);
        return $message . $this->packCRC($message);
    }

    /**
     * Prepara uma mensagem que utiliza a função Modbus 16.
     *
     * @param int $address
     *            Endereço da rede.
     * @param int $startReg
     *            Registro inicial da escrita.
     * @param array $regs
     *            Registros para escrita.
     * @return string Mensagem Modbus.
     * @throws \Exception
     */
    public function pack16(int $address, int $startReg, int ...$regs): string
    {
        $header = $this->packHeader($address, 16, $startReg, count($regs));
        $content = $this->packContent(...$regs);
        $message = $header . $content;

        return $message . $this->packCRC($message);
    }

    /**
     * Prepara uma mensagem que utiliza a função Modbus 23.
     *
     * @param int $address
     *            Endereço da rede.
     * @param int $startReadReg
     *            Registro inicial da leitura.
     * @param int $totalReadRegs
     *            Número total de registros para leitura.
     * @param int $startWriteReg
     *            Registro inicial da escrita.
     * @param array $regs
     *            Registros para escrita.
     * @return string Mensagem Modbus.
     * @throws \Exception
     */
    public function pack23(int $address, int $startReadReg, int $totalReadRegs, int $startWriteReg, int ...$regs): string
    {
        $header = $this->packHeader($address, 23, $startReadReg, $totalReadRegs) .
             $this->packHeaderRange($startWriteReg, count($regs));

        $content = $this->packContent(...$regs);
        $message = $header . $content;

        return $message . $this->packCRC($message);
    }

    /**
     * Extrai as informações de uma mensagem Modbus válida.
     *
     * @param string $message
     *            Mensagem Modbus.
     * @param int $address
     *            Endereço de rede esperado.
     * @param int $func
     *            Código da função esperada.
     * @param string|NULL $mask
     *            Máscara de formatação das informações de resposta.
     * @return array
     * @throws \Exception
     */
    public function &unpack(string $message, int $address, int $func, string $mask = NULL): array
    {
        $this->validateCRC($message);
        $this->validateMessage($message, $address, $func);

        $bytes = (ord($message[2]) & 0x00FF);
        $words = (int) ($bytes / 2);

        $result = [
            'size' => strlen($message),
            'bytes' => $bytes,
            'words' => $words
        ];

        if ($words > 0 && ! empty($mask)) {
            $result['data'] = unpack($mask, substr($message, 3, $bytes));
        } else {
            $result['data'] = [];
        }

        return $result;
    }
}