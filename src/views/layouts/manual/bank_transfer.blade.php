@inject('GeneralHelper', 'budisteikul\toursdk\Helpers\GeneralHelper')
<html>
<head><meta http-equiv=Content-Type content="text/html; charset=UTF-8">
<style type="text/css">
<!--
span.cls_003{font-family: Arial, Helvetica, sans-serif;font-size:16.1px;color:rgb(102,102,102);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_003{font-family: Arial, Helvetica, sans-serif;font-size:16.1px;color:rgb(102,102,102);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_004{font-family: Arial, Helvetica, sans-serif;font-size:10.1px;color:rgb(153,153,153);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_004{font-family: Arial, Helvetica, sans-serif;font-size:10.1px;color:rgb(153,153,153);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_002{font-family: Arial, Helvetica, sans-serif;font-size:10.1px;color:rgb(3,172,218);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_002{font-family: Arial, Helvetica, sans-serif;font-size:10.1px;color:rgb(3,172,218);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_005{font-family: Arial, Helvetica, sans-serif;font-size:14.1px;color:rgb(102,101,102);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_005{font-family: Arial, Helvetica, sans-serif;font-size:14.1px;color:rgb(102,101,102);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_014{font-family: Arial, Helvetica, sans-serif;font-size:13.1px;color:rgb(84,84,84);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_014{font-family: Arial, Helvetica, sans-serif;font-size:13.1px;color:rgb(84,84,84);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_006{font-family: Arial, Helvetica, sans-serif;font-size:12.1px;color:rgb(255,254,255);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_006{font-family: Arial, Helvetica, sans-serif;font-size:12.1px;color:rgb(255,254,255);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_011{font-family: Arial, Helvetica, sans-serif;font-size:18.1px;color:rgb(102,101,102);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_011{font-family: Arial, Helvetica, sans-serif;font-size:18.1px;color:rgb(102,101,102);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_007{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(68,68,68);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_007{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(68,68,68);font-weight:normal;font-style:normal;text-decoration: none}
span.cls_008{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_008{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_009{font-family: Arial, Helvetica, sans-serif;font-size:14.0px;color:rgb(255,254,255);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_009{font-family: Arial, Helvetica, sans-serif;font-size:14.0px;color:rgb(255,254,255);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_010{font-family: Arial, Helvetica, sans-serif;font-size:16.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_010{font-family: Arial, Helvetica, sans-serif;font-size:16.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_012{font-family: Arial, Helvetica, sans-serif;font-size:15.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
div.cls_012{font-family: Arial, Helvetica, sans-serif;font-size:15.1px;color:rgb(46,128,193);font-weight:bold;font-style:normal;text-decoration: none}
span.cls_013{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(68,68,68);font-weight:normal;font-style:normal;text-decoration: none}
div.cls_013{font-family: Arial, Helvetica, sans-serif;font-size:11.1px;color:rgb(68,68,68);font-weight:normal;font-style:normal;text-decoration: none}
html,body {
    margin:0;
    padding:0;
}
-->
</style>
</head>
<body>
<div style="position:absolute;left:50%;margin-left:-290px;top:0px;width:580px;height:2732px;border-style:outset;overflow:hidden">
<div style="position:absolute;left:0px;top:0px">
<img src="{{url('/img/background1.jpg')}}" width=580 height=2732></div>
<div style="position:absolute;left:68.03px;top:25.83px" class="cls_003">
	<img src="{{url('/img/logo-blue.jpg')}}" height="30" />
</div>
<div style="position:absolute;left:200.59px;top:66.52px" class="cls_004">
	<span class="cls_004">{{ Carbon\Carbon::parse($shoppingcart->shoppingcart_payment->updated_at)->formatLocalized('%d %b %Y %H:%M') }}</span>
</div>
<div style="position:absolute;left:66.13px;top:67.25px" class="cls_002">
	<span class="cls_002">Transaction Time</span>
</div>
<div style="position:absolute;left:190.25px;top:67.25px" class="cls_002">
	<span class="cls_002">:</span>
</div>
<div style="position:absolute;left:66.13px;top:109.88px" class="cls_005">
	<span class="cls_005">Order ID</span>
</div>
<div style="position:absolute;left:190.25px;top:109.88px" class="cls_005">
	<span class="cls_005">:</span>
</div>
<div style="position:absolute;left:200.59px;top:111.13px" class="cls_014">
	<span class="cls_014">{{$shoppingcart->confirmation_code}}</span>
</div>
<div style="position:absolute;left:66.13px;top:130.27px" class="cls_005">
	<span class="cls_005">Batas Pembayaran :</span>
</div>
<div style="position:absolute;left:200.59px;top:132.39px" class="cls_014">
	<span class="cls_014">{{ Carbon\Carbon::parse($shoppingcart->shoppingcart_payment->updated_at)->addDays(1)->formatLocalized('%d %b %Y %H:%M') }}</span>
</div>
<div style="position:absolute;left:66.13px;top:150.67px" class="cls_005">
	<span class="cls_005">Total Pembayaran</span>
</div>
<div style="position:absolute;left:190.25px;top:150.67px" class="cls_005">
	<span class="cls_005">:</span>
</div>
<div style="position:absolute;left:200.59px;top:152.23px" class="cls_014">
	<span class="cls_014">{{ $GeneralHelper->formatRupiah($shoppingcart->shoppingcart_payment->amount) }}</span>
</div>

<div style="position:absolute;left:66.13px;top:171.07px" class="cls_005">
	<span class="cls_005">Nama Bank</span>
</div>
<div style="position:absolute;left:190.25px;top:171.07px" class="cls_005">
	<span class="cls_005">:</span>
</div>
<div style="position:absolute;left:200.59px;top:173.20px" class="cls_014">
	<span class="cls_014">
		{{strtoupper($shoppingcart->shoppingcart_payment->bank_name)}} ({{$shoppingcart->shoppingcart_payment->bank_code}})
	</span>
</div>

<div style="position:absolute;left:66.13px;top:191.47px" class="cls_005">
	<span class="cls_005">No. Rekening</span>
</div>
<div style="position:absolute;left:190.25px;top:191.47px" class="cls_005">
	<span class="cls_005">:</span>
</div>
<div style="position:absolute;left:200.59px;top:193.60px" class="cls_014">
	<span class="cls_014">{{ $GeneralHelper->splitSpace($shoppingcart->shoppingcart_payment->va_number,4,5) }}</span>
</div>

<div style="position:absolute;left:205.78px;top:269.67px" class="cls_006"><span class="cls_006">1</span></div>
<div style="position:absolute;left:392.78px;top:269.67px" class="cls_006"><span class="cls_006">2</span></div>
<div style="position:absolute;left:46.42px;top:269.90px" class="cls_011"><span class="cls_011">Cara</span></div>
<div style="position:absolute;left:46.42px;top:290.29px" class="cls_011"><span class="cls_011">Pembayaran</span></div>
<div style="position:absolute;left:207.00px;top:371.50px" class="cls_007"><span class="cls_007">Pada menu utama, pilih</span></div>
<div style="position:absolute;left:395.50px;top:372.00px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Transfer</span></div>
<div style="position:absolute;left:46.42px;top:378.04px" class="cls_007"><span class="cls_007">Bayar dari ATM</span></div>
<div style="position:absolute;left:207.00px;top:386.30px" class="cls_008"><span class="cls_008">Transaksi Lainnya</span></div>
<div style="position:absolute;left:46.42px;top:391.24px" class="cls_007"><span class="cls_007">bank-bank berikut:</span></div>
<div style="position:absolute;left:205.78px;top:425.34px" class="cls_009"><span class="cls_009">3</span></div>
<div style="position:absolute;left:392.78px;top:425.67px" class="cls_006"><span class="cls_006">4</span></div>
<div style="position:absolute;left:392.85px;top:452.95px" class="cls_010"><span class="cls_010">{{$shoppingcart->shoppingcart_payment->bank_code}}</span></div>
<div style="position:absolute;left:393.00px;top:527.49px" class="cls_007"><span class="cls_007">Masukkan nomor</span><span class="cls_008"> {{$shoppingcart->shoppingcart_payment->bank_code}}</span><span class="cls_007"> (Kode Bank</span></div>
<div style="position:absolute;left:207.00px;top:527.50px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Rekening Bank Lain</span></div>
<div style="position:absolute;left:393.00px;top:542.69px" class="cls_007"><span class="cls_007">{{strtoupper($shoppingcart->shoppingcart_payment->bank_name)}}), lalu tekan </span><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:205.78px;top:592.67px" class="cls_006"><span class="cls_006">5</span></div>
<div style="position:absolute;left:392.78px;top:592.67px" class="cls_006"><span class="cls_006">6</span></div>
<div style="position:absolute;left:205.80px;top:625.03px" class="cls_012">
	<span class="cls_012">{{ $GeneralHelper->formatRupiah($shoppingcart->shoppingcart_payment->amount) }}</span>
</div>
<div style="position:absolute;left:394.58px;top:624.46px" class="cls_012">
	<span class="cls_012">{{ $GeneralHelper->splitSpace($shoppingcart->shoppingcart_payment->va_number,4,5) }}</span>
</div>
<div style="position:absolute;left:207.00px;top:694.50px" class="cls_008"><span class="cls_008">Masukkan jumlah tagihan</span></div>
<div style="position:absolute;left:393.01px;top:694.52px" class="cls_007"><span class="cls_007">Masukkan {{strlen($shoppingcart->shoppingcart_payment->va_number)}} digit</span></div>
<div style="position:absolute;left:207.00px;top:709.30px" class="cls_008"><span class="cls_008">yang akan anda bayar secara</span></div>
<div style="position:absolute;left:393.00px;top:709.32px" class="cls_007"><span class="cls_007">Nomor Rekening, lalu tekan</span></div>
<div style="position:absolute;left:207.00px;top:724.09px" class="cls_008"><span class="cls_008">lengkap.</span></div>
<div style="position:absolute;left:393.00px;top:724.52px" class="cls_008"><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:207.00px;top:740.90px" class="cls_013"><span class="cls_013">Pembayaran dengan jumlah tidak</span></div>
<div style="position:absolute;left:207.00px;top:755.70px" class="cls_013"><span class="cls_013">sesuai akan otomatis ditolak</span></div>
<div style="position:absolute;left:205.78px;top:833.67px" class="cls_006"><span class="cls_006">7</span></div>
<div style="position:absolute;left:207.00px;top:935.50px" class="cls_007"><span class="cls_007">Pada halaman konfirmasi</span></div>
<div style="position:absolute;left:207.00px;top:948.30px" class="cls_007"><span class="cls_007">transfer akan muncul jumlah</span></div>
<div style="position:absolute;left:207.00px;top:961.11px" class="cls_007"><span class="cls_007">yang dibayarkan, nomor</span></div>
<div style="position:absolute;left:207.00px;top:973.91px" class="cls_007"><span class="cls_007">rekening dan nama anda.</span></div>
<div style="position:absolute;left:207.00px;top:986.72px" class="cls_007"><span class="cls_007">Jika informasi telah sesuai</span></div>
<div style="position:absolute;left:207.00px;top:999.52px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:205.78px;top:1105.67px" class="cls_006"><span class="cls_006">1</span></div>
<div style="position:absolute;left:392.78px;top:1105.67px" class="cls_006"><span class="cls_006">2</span></div>
<div style="position:absolute;left:46.42px;top:1105.90px" class="cls_011"><span class="cls_011">Cara</span></div>
<div style="position:absolute;left:46.42px;top:1126.29px" class="cls_011"><span class="cls_011">Pembayaran</span></div>
<div style="position:absolute;left:207.00px;top:1207.50px" class="cls_007"><span class="cls_007">Pada menu utama, pilih</span></div>
<div style="position:absolute;left:395.50px;top:1208.00px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Transfer</span></div>
<div style="position:absolute;left:46.42px;top:1220.10px" class="cls_007"><span class="cls_007">Bayar dari ATM</span></div>
<div style="position:absolute;left:207.00px;top:1222.30px" class="cls_008"><span class="cls_008">Transaksi Lainnya</span></div>
<div style="position:absolute;left:46.42px;top:1233.30px" class="cls_007"><span class="cls_007">bank-bank berikut:</span></div>
<div style="position:absolute;left:205.78px;top:1261.34px" class="cls_009"><span class="cls_009">3</span></div>
<div style="position:absolute;left:392.78px;top:1261.67px" class="cls_006"><span class="cls_006">4</span></div>
<div style="position:absolute;left:392.85px;top:1288.95px" class="cls_010"><span class="cls_010">{{$shoppingcart->shoppingcart_payment->bank_code}}&nbsp;{{ $GeneralHelper->splitSpace($shoppingcart->shoppingcart_payment->va_number,4,5) }}</span>
</div>
<div style="position:absolute;left:393.00px;top:1363.49px" class="cls_007"><span class="cls_007">Masukkan nomor</span><span class="cls_008"> {{$shoppingcart->shoppingcart_payment->bank_code}}</span><span class="cls_007"> dan</span></div>
<div style="position:absolute;left:207.00px;top:1363.50px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Antar Bank Online</span></div>
<div style="position:absolute;left:392.99px;top:1378.29px" class="cls_007"><span class="cls_007">{{strlen($shoppingcart->shoppingcart_payment->va_number)}} digit Nomor Rekening, lalu</span></div>
<div style="position:absolute;left:392.99px;top:1393.49px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:205.78px;top:1428.67px" class="cls_006"><span class="cls_006">5</span></div>
<div style="position:absolute;left:392.78px;top:1428.67px" class="cls_006"><span class="cls_006">6</span></div>
<div style="position:absolute;left:205.80px;top:1457.85px" class="cls_012">
	<span class="cls_012">{{ $GeneralHelper->formatRupiah($shoppingcart->shoppingcart_payment->amount) }}</span>
</div>
<div style="position:absolute;left:207.00px;top:1530.50px" class="cls_008"><span class="cls_008">Masukkan jumlah tagihan</span></div>
<div style="position:absolute;left:393.01px;top:1530.52px" class="cls_007"><span class="cls_007">Kosongkan No. Referensi, lalu</span></div>
<div style="position:absolute;left:207.00px;top:1545.30px" class="cls_008"><span class="cls_008">yang akan anda bayar secara</span></div>
<div style="position:absolute;left:393.01px;top:1545.72px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Correct</span></div>
<div style="position:absolute;left:207.00px;top:1560.09px" class="cls_008"><span class="cls_008">lengkap.</span></div>
<div style="position:absolute;left:207.00px;top:1576.90px" class="cls_013"><span class="cls_013">Pembayaran dengan jumlah tidak</span></div>
<div style="position:absolute;left:207.00px;top:1591.70px" class="cls_013"><span class="cls_013">sesuai akan otomatis ditolak</span></div>
<div style="position:absolute;left:205.78px;top:1669.67px" class="cls_006"><span class="cls_006">7</span></div>
<div style="position:absolute;left:207.00px;top:1771.50px" class="cls_007"><span class="cls_007">Pada halaman konfirmasi</span></div>
<div style="position:absolute;left:207.00px;top:1784.30px" class="cls_007"><span class="cls_007">transfer akan muncul jumlah</span></div>
<div style="position:absolute;left:207.00px;top:1797.11px" class="cls_007"><span class="cls_007">yang dibayarkan, nomor</span></div>
<div style="position:absolute;left:207.00px;top:1809.91px" class="cls_007"><span class="cls_007">rekening dan nama anda.</span></div>
<div style="position:absolute;left:207.00px;top:1822.72px" class="cls_007"><span class="cls_007">Jika informasi telah sesuai</span></div>
<div style="position:absolute;left:207.00px;top:1835.52px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:205.78px;top:1943.17px" class="cls_006"><span class="cls_006">1</span></div>
<div style="position:absolute;left:392.78px;top:1943.17px" class="cls_006"><span class="cls_006">2</span></div>
<div style="position:absolute;left:46.42px;top:1943.40px" class="cls_011"><span class="cls_011">Cara</span></div>
<div style="position:absolute;left:46.42px;top:1963.79px" class="cls_011"><span class="cls_011">Pembayaran</span></div>
<div style="position:absolute;left:207.00px;top:2045.00px" class="cls_007"><span class="cls_007">Pada menu utama, pilih</span></div>
<div style="position:absolute;left:395.50px;top:2045.50px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Transfer</span></div>
<div style="position:absolute;left:46.42px;top:2054.31px" class="cls_007"><span class="cls_007">Bayar dari ATM</span></div>
<div style="position:absolute;left:207.00px;top:2059.79px" class="cls_008"><span class="cls_008">Transaksi Lainnya</span></div>
<div style="position:absolute;left:46.42px;top:2067.51px" class="cls_007"><span class="cls_007">bank-bank berikut:</span></div>
<div style="position:absolute;left:205.78px;top:2098.84px" class="cls_009"><span class="cls_009">3</span></div>
<div style="position:absolute;left:392.46px;top:2099.00px" class="cls_006"><span class="cls_006">4</span></div>
<div style="position:absolute;left:392.53px;top:2126.27px" class="cls_010"><span class="cls_010">{{$shoppingcart->shoppingcart_payment->bank_code}}</span></div>
<div style="position:absolute;left:207.00px;top:2201.00px" class="cls_007"><span class="cls_007">Pilih </span><span class="cls_008">Rekening Bank Lain</span></div>
<div style="position:absolute;left:392.69px;top:2200.82px" class="cls_007"><span class="cls_007">Masukkan nomor</span><span class="cls_008"> {{$shoppingcart->shoppingcart_payment->bank_code}}</span><span class="cls_007"> lalu</span></div>
<div style="position:absolute;left:392.68px;top:2213.82px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Benar</span></div>
<div style="position:absolute;left:205.78px;top:2266.17px" class="cls_006"><span class="cls_006">5</span></div>
<div style="position:absolute;left:392.78px;top:2266.17px" class="cls_006"><span class="cls_006">6</span></div>
<div style="position:absolute;left:205.80px;top:2295.77px" class="cls_012">
	<span class="cls_012">{{ $GeneralHelper->formatRupiah($shoppingcart->shoppingcart_payment->amount) }}</span>
</div>
<div style="position:absolute;left:394.58px;top:2295.77px" class="cls_012">
	<span class="cls_012">{{ $GeneralHelper->splitSpace($shoppingcart->shoppingcart_payment->va_number,4,5) }}</span>
</div>
<div style="position:absolute;left:207.00px;top:2368.00px" class="cls_008"><span class="cls_008">Masukkan jumlah tagihan</span></div>
<div style="position:absolute;left:394.00px;top:2370.00px" class="cls_007"><span class="cls_007">Masukkan {{strlen($shoppingcart->shoppingcart_payment->va_number)}} digit Nomor</span></div>
<div style="position:absolute;left:483.73px;top:2370.00px" class="cls_007"></div>
<div style="position:absolute;left:207.00px;top:2382.80px" class="cls_008"><span class="cls_008">yang akan anda bayar secara</span></div>
<div style="position:absolute;left:393.92px;top:2384.80px" class="cls_007"><span class="cls_007">Rekening, lalu tekan</span><span class="cls_008"> Benar</span></div>
<div style="position:absolute;left:207.00px;top:2397.59px" class="cls_008"><span class="cls_008">lengkap.</span></div>
<div style="position:absolute;left:207.00px;top:2414.40px" class="cls_013"><span class="cls_013">Pembayaran dengan jumlah tidak</span></div>
<div style="position:absolute;left:207.00px;top:2429.20px" class="cls_013"><span class="cls_013">sesuai akan otomatis ditolak</span></div>
<div style="position:absolute;left:205.78px;top:2507.17px" class="cls_006"><span class="cls_006">7</span></div>
<div style="position:absolute;left:207.00px;top:2609.00px" class="cls_007"><span class="cls_007">Pada halaman konfirmasi</span></div>
<div style="position:absolute;left:207.00px;top:2621.80px" class="cls_007"><span class="cls_007">transfer akan muncul jumlah</span></div>
<div style="position:absolute;left:207.00px;top:2634.61px" class="cls_007"><span class="cls_007">yang dibayarkan, nomor</span></div>
<div style="position:absolute;left:207.00px;top:2647.41px" class="cls_007"><span class="cls_007">rekening dan nama anda.</span></div>
<div style="position:absolute;left:207.00px;top:2660.22px" class="cls_007"><span class="cls_007">Jika informasi telah sesuai</span></div>
<div style="position:absolute;left:207.00px;top:2673.02px" class="cls_007"><span class="cls_007">tekan </span><span class="cls_008">Benar</span></div>
</div>

</body>
</html>
