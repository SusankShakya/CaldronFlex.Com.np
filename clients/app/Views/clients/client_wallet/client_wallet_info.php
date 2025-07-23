<?php
$total_client_wallet_amount = $client_wallet_summary->total_client_wallet_amount ? $client_wallet_summary->total_client_wallet_amount : 0;
$total_distributed_amount = $client_wallet_summary->total_distributed_amount ? $client_wallet_summary->total_distributed_amount : 0;
$balance = $client_wallet_summary->balance ? $client_wallet_summary->balance : 0;
?>

<div class="card">
    <div class="box">
        <div class="box-content">
            <div class="pt-3 pb-3 text-center">
                <div class="b-r">
                    <h4 class="strong mb-1 mt-0 <?php echo $total_client_wallet_amount == 0 ? 'text-off text-default' : ''; ?>" style="color: #6C757D;"><?php echo to_currency($total_client_wallet_amount, $currency_symbol); ?></h4>
                    <span><?php echo app_lang("received_payments"); ?></span>
                </div>
            </div>
        </div>
        <div class="box-content">
            <a href="#" class="text-default" id="total-distributed-amount">
                <div class="pt-3 pb-3 text-center">
                    <div class="b-r">
                        <h4 class="strong mb-1 mt-0 <?php echo $total_distributed_amount == 0 ? 'text-off text-default' : 'text-primary'; ?>"><?php echo to_currency($total_distributed_amount, $currency_symbol); ?></h4>
                        <span><?php echo app_lang("allocated_payments"); ?></span>
                    </div>
                </div>
            </a>
        </div>
        <div class="box-content">
            <div class="pt-3 pb-3 text-center">
                <div class="b-r">
                    <h4 class="strong mb-1 mt-0 <?php echo $balance == 0 ? 'text-off text-default' : ''; ?>" style="color: #01B393;"><?php echo to_currency($balance, $currency_symbol); ?></h4>
                    <span><?php echo app_lang("balance"); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#total-distributed-amount").on("click", function() {
            $("#client-payments-tabs [data-bs-target='#payments-list-tab']").trigger("click");
        })
    });
</script>