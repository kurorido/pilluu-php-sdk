## 紅陽 BNPL 皮路 PHP SDK

### Install

```
composer require kurorido/pilluu-php-sdk
```

### 傳送交易 Cashier

```
$testing = true; // 設定環境
$cashier = new PilluCashier('商戶編號', 'Public Key' , 'SHA2 Key', $testing);

$order = (new PilluOrder)
    ->setTradeId('商戶訂單號')
    ->setAmount('888')
    ->setMemo('測試商品');

$autoSubmit = false; // 是否使用 JavaScript 自動送出?
$cashier->doCheckout($order, $autoSubmit);
```

### 接收付款結果 Callback

目前只檢查 content 與 merchantid 是否符合，如有需要請自行繼承覆寫 check

```
$callback = new PilluCallback('商戶編號', 'Public Key' , 'SHA2 Key');
$response = $callback->check($payload);

if ($response->body->result === 'success') {
    // 付款成功，官方建議再發 Double Check
}
```

### 訂單查詢 Double Check

```
$testing = true; // 設定環境
$checker = new PilluCheckOrder('商戶編號', 'Public Key' , 'SHA2 Key', $testing);

$response = $checker->doCheck('交易發起時間', '交易金額', '商戶訂單號 tradeid');

if ($response->result === '111') {
    // 付款成功
}
```
