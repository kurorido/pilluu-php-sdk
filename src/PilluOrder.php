<?php

namespace Pillu;

class PilluOrder
{
    public string $merchantid = '';
    public string $tradeid = '';
    public string $tradetime = '';
    public string $amount = '';
    public string $memo = '';
    public string $installment = '0';
    public string $fee = '0';

    public function setTradeId(string $tradeid)
    {
        $this->$tradeid = $tradeid;
        return $this;
    }

    public function setMerchantId(string $merchantid)
    {
        $this->$merchantid = $merchantid;
        return $this;
    }

    public function setTradeTime(string $tradetime)
    {
        $this->$tradetime = $tradetime;
        return $this;
    }

    public function setAmount(string $amount)
    {
        $this->$amount = $amount;
        return $this;
    }

    public function setMemo(string $memo)
    {
        $this->$memo = $memo;
        return $this;
    }
}