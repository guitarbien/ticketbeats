<a href="#"><img src="https://travis-ci.org/guitarbien/ticketbeats.svg?branch=master" alt="Build Status"></a>
[![Coverage Status](https://coveralls.io/repos/github/guitarbien/ticketbeats/badge.svg?branch=master)](https://coveralls.io/github/guitarbien/ticketbeats?branch=master)

## TescCases
- Features
    - PurchaseTicketsTest.php
        * 使用者可以購票
        * 不能購買尚未發佈的票
        * 不能超量購買
        * 票券若是在嘗試購買中則不能再被購買
        * 若付款失敗則不會產生訂單
        * 下單時 email 為必填
        * 驗證 email 格式
        * 票券數量為必填
        * 票券數量至少要為 1
        * token 為必填
    - ViewConcertListingTest.php
        * 使用者可以看到已發佈的音樂會 lists 頁面
        * 若音樂會尚未發佈則使用者只能看到 404 頁面
    - ViewOrderTest.php
        * 使用者可以查看訂單確認頁
- Unit
    - Billing
        - FakePaymentGatewayTest.php
            * 在第一次付款前執行 hook
        - OrderTest.php
            * 用票券和 email 和付款物件建立訂單
            * 用確認碼取得訂單資訊
            * 用確認碼查詢不存在的訂單資訊拋出例外
            * 轉換成 array
        - PaymentGatewayContractTests.php
            * 以合法 token 付款成功
            * 成功付款之後可以得到詳細資訊
            * 可以透過 callback 取得付款的物件
            * 以不合法 token 付款失敗
        - ReservationTest.php
            * 計算總金額
            * 取得被保留的票券
            * 取得客戶 email
            * 取消保留後保留票券應也要被釋出
            * 完成保留操作
        - StripePaymentGatewayTest.php
            * (和 FakePaymentGatewayTest 共用 PaymentGatewayContractTests，此處只回傳 payment 物件)
        - TicketTest.php
            * 票券能被保留
            * 票券可以被釋出
    - ConcertTeset.php
        * 取得格式化的日期
        * 取得格式化的開始時間
        * 以美元顯示票價
        * published_at 有值的資料就是已經發佈了
        * 可以加入票券
        * 可購買的票券不應該包含已有訂單的票券
        * 保留超過可購買的票券數量會拋出例外
        * 能保留可用票券
        * 已經被購買的票券不能被保留
        * 已經被保留的票券不能再被保留
    - RandomOrderConfirmationNumberGeneratorTest.php
        * 確認碼長度要為 24
        * 確認碼只能有大寫英文字和數字
        * 確認碼不能有模糊字元
        * 確認碼要是不重複唯一值