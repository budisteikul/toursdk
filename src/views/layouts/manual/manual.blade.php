
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">   
<title>Invoice-{{ $shoppingcart->confirmation_code }}</title>
<style type="text/css" media="all">
body {
  margin: 0 auto; 
  color: #555555;
  background: #FFFFFF; 
  font-family: Arial, Helvetica, sans-serif;
  font-size: 12px; 
}

table {
  width: 100%;
  border-collapse: collapse;
  border-spacing: 0;
  margin-bottom: 20px;
}

table th,
table td {
  padding: 20px;
  background: #EEEEEE;
  text-align: center;
  border-bottom: 1px solid #FFFFFF;
  border-top: 1px solid #FFFFFF;
}

table th {
  white-space: nowrap;        
  font-weight: normal;
}

table td {
  text-align: right;
}

table td h3{
  color: #0087C3;
  font-size: 1.2em;
  font-weight: normal;
  margin: 0 0 0.2em 0;
}

table .no {
  color: #FFFFFF;
  font-size: 1.6em;
  background: #0087C3;
  -webkit-print-color-adjust: exact; 
}

table .desc {
  text-align: left;
  -webkit-print-color-adjust: exact; 
}

table .unit {
  background: #DDDDDD;
  -webkit-print-color-adjust: exact; 
}

table .qty {
	-webkit-print-color-adjust: exact; 
}

table .total {
  background: #0087C3;
  color: #FFFFFF;
  -webkit-print-color-adjust: exact; 
}

table td.unit,
table td.qty,
table td.total,
table tbody tr:last-child td {
  border-bottom: 1px solid #FFFFFF;
  border-top: 1px solid #FFFFFF;
}

table tfoot td {
  padding: 10px 20px;
  background: #FFFFFF;
  border-bottom: none;
  font-size: 1.2em;
  white-space: nowrap; 
  border-top: none; 
}

table tfoot tr:first-child td {
  border-top: none; 
}

table tfoot tr:last-child td {
  color: #0087C3;
  font-size: 1.4em;
  border-top: 1px solid #0087C3; 

}

table tfoot tr td:first-child {
  border: none;
}

a {
  color: #0087C3;
  text-decoration: none;
}

#client {
  padding-left: 6px;
  border-left: 6px solid #0087C3;
}

#client .to {
  color: #777777;
}

h2.name {
  font-size: 1.4em;
  font-weight: normal;
  margin: 0;
}




#invoice {
  text-align: right;
}

#invoice h1 {
  color: #0087C3;
  font-size: 2.4em;
  line-height: 1em;
  font-weight: normal;
  margin-bottom:7px;
}

#invoice .date {
  font-size: 1.1em;
  color: #777777;
}

#thanks{
  font-size: 1.0em;
  margin-bottom: 20px;
}

#notices{
  padding: 6px 6px 6px 6px;
  border: 1px dashed #CCCCCC;
  width: 40%;
}

#notices .notice {
  font-size: 1.2em;
}

footer {
  color: #777777;
  width: 100%;
  height: 30px;
  border-top: 1px solid #AAAAAA;
  padding: 8px 0;
  text-align: center;
  
  display: block;
    position: fixed;
    bottom: 0;
}

.invoice-hilang
{
  display: none;
}

.invoice-color-danger
{
  color: #d9534f;
  font-weight: bold;
}

.invoice-color-success
{
  color: #5cb85c;
  font-weight: bold;
}

.invoice-color-warning
{
  color: #f0ad4e;
  font-weight: bold;
}

</style>
</head>
<body>
    
      
     <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom:0px;">
       <tbody>
         <tr>
           <td style="background-color:#FFFFFF; text-align:left; padding-left:0px;">
           
           				
           </td>
           <td style="background-color:#FFFFFF; text-align:right; padding-right:0px; line-height: 18px; font-size:14px; color:#777777">
                        <img src="{{ config('site.assets') }}/img/pdf/logo-blue.jpg" height="30" />
                        <div style="margin-top:3px;">Jl. Abiyoso VII No.190 Bantul ID</div>
                        <div>Telp: +62 857 43 112 112</div>
                        <div>Email: guide@vertikaltrip.com</div>
           </td>
         </tr>
       </tbody>
     </table>
     
<hr style="border:none; height:1px; color: #AAA; background-color: #AAA; margin-top:0px; margin-bottom:5px;">


@if($shoppingcart->shoppingcart_payment->bank_code=="022")
<center><h1 style="margin-top:2px;">How to Pay CIMB NIAGA Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">Octo Mobile</h2>1. Login ke Octo Mobile<br>2. Pilih menu : Transfer<br>3. Pilih menu : Transfer to Other CIMB Niaga Account<br>4. Pilih Source of fund<br>5. Masukkan nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }}<br>6. Masukkan jumlah pembayaran (Amount) sesuai tagihan<br>7. Klik tombol Next<br>8. Nomor virtual account, nama virtual account dan jumlah pembayaran (Amount) ditampilkan pada layar<br>9. Klik tombol Confirm<br>10. Masukkan Mobile Banking PIN<br><h2 style="margin-bottom:0px;">Internet Banking Bank Lain</h2>1. Login ke internet banking<br>2. Pilih menu transfer ke Bank Lain Online<br>3. Pilih bank tujuan Bank CIMB Niaga (kode bank: {{ $shoppingcart->shoppingcart_payment->bank_code }}) <br>4. Masukkan nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }}<br>5. Masukkan jumlah pembayaran sesuai tagihan<br>6. Nomor, nama virtual account dan jumlah billing ditampilkan pada layar<br>7. Ikuti instruksi untuk menyelesaikan transaksi<br>8. Konfirmasi pembayaran ditampilkan pada layar<br><h2 style="margin-bottom:0px;">OCTO Clicks</h2>1. Login ke OCTO Clicks (https://www.octoclicks.co.id)<br>2. Pilih menu : Pembayaran Tagihan<br>3. Pilih kategori transaksi : Virtual Account<br>4. Pilih rekening sumber dana<br>5. Masukkan nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }} dan klik tombol : Lanjutkan untuk verifikasi detail<br>6. Nomor virtual account, nama virtual account dan total tagihan ditampilkan pada layar<br>7. Masukkan 6 digit OTP dan tekan tombol Submit<br>8. Klik tombol Konfirmasi untuk memproses pembayaran<br><h2 style="margin-bottom:0px;">ATM Alto / Bersama / Prima</h2>1. Masukkan Kartu ATM dan PIN Anda pada mesin ATM bank tersebut<br>2. Pilih menu TRANSFER > TRANSFER KE BANK LAIN<br>3. Masukkan kode bank CIMB Niaga: {{ $shoppingcart->shoppingcart_payment->bank_code }}<br>4. Masukkan jumlah pembayaran sesuai tagihan<br>5. Masukkan nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }}<br>6. Ikuti instruksi untuk menyelesaikan transaksi<br>7. Konfirmasi pembayaran ditampilkan pada layar<br><h2 style="margin-bottom:0px;">ATM CIMB</h2>1. Masukkan Kartu ATM dan PIN CIMB Anda<br>2. Pilih menu Pembayaran > Lanjut > Virtual Account<br>3. Masukkan nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }}<br>4. Pilih rekening debit<br>5. Nomor, nama virtual account dan jumlah billing ditampilkan pada layar<br>6. Pilih OK untuk melakukan pembayaran<br>7. Konfirmasi pembayaran ditampilkan pada layar<br>
@endif

@if($shoppingcart->shoppingcart_payment->bank_code=="011")
<center><h1 style="margin-top:2px;">How to Pay DANAMON Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">ATM Danamon</h2>1. Masukkan kartu ATM Danamon, lalu masukkan PIN ATM Anda<br>2. Pilih menu > "Pembayaran" > "Lainnya" > "Virtual Account"<br>3. Masukan 16 digit nomor virtual account ({{ $shoppingcart->shoppingcart_payment->va_number }})<br>4. Pastikan data pembayaran sudah benar dan pilih "YA"<br>5. Transaksi selesai, harap simpan bukti transaksi Anda<br><h2 style="margin-bottom:0px;">D-Mobile (M-Banking)</h2>1. Akses D-Mobile melalui smartphone Anda<br>2. Masukkan User ID dan Password<br>3. Pilih menu > "Pembayaran" > "Virtual Account"<br>4. Tambahkan biller baru pembayaran<br>5. Masukan 16 digit nomor virtual account ({{ $shoppingcart->shoppingcart_payment->va_number }})<br>6. Pastikan data pembayaran sudah benar<br>7. Masukkan mPIN Anda untuk konfirmasi<br>8. Transaksi selesai, Anda akan mendapatkan info transaksi<br><h2 style="margin-bottom:0px;">ATM Bersama/ATM Alto/ATM Prima (Transfer)</h2>1. Masuk ke menu > "Transfer"<br>2. Pilih > "Transfer ke bank lain"<br>3. Masukkan kode Bank Danamon : 011 + 16 digit nomor virtual account di rekening tujuan ({{ $shoppingcart->shoppingcart_payment->bank_code }}{{ $shoppingcart->shoppingcart_payment->va_number }})<br>4. Masukkan jumlah pembayaran sesuai tagihan<br>5. Pastikan data pembayaran sudah benar<br>6. Pilih > "YA", untuk melanjutkan transaksi<br>7. Transaksi selesai, harap simpan bukti transaksi Anda<br>
@endif

@if($shoppingcart->shoppingcart_payment->bank_code=="013")
<center><h1 style="margin-top:2px;">How to Pay PERMATA Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">ATM PRIMA/ALTO</h2>1. Masukkan PIN<br>2. Pilih "Transfer". Jika menggunakan ATM bank lain, pilih "Pembayaran Lainnya"<br>3. Pilih pembayaran lainnya<br>4. Masukkan kode bank Permata ({{ $shoppingcart->shoppingcart_payment->bank_code }}) diikuti 16 digit nomor virtual account anda: {{ $shoppingcart->shoppingcart_payment->va_number }} kemudian pilih "Benar"<br>5. Masukkan nominal yang akan dibayarkan. Pembayaran transaksi gagal akan muncul jika nominal yang dimasukkan salah<br>6. Konfirmasi kode bank, nomor virtual account dan nominal pembayaran, jika sudah sesuai pilih "Benar"<br>7. Transaksi anda selesai<br><h2 style="margin-bottom:0px;">Internet Banking Permata (PermataNet)</h2>1. Login ke akun internet banking<br>2. Pilih menu “pembayaran” dan pilih "Pembayaran Lainnya. Masukkan kode bank Permata ({{ $shoppingcart->shoppingcart_payment->bank_code }}) sebagai bank tujuan"<br>3. Masukkan nominal transaksi<br>4. Masukkan 16 digit nomor virtual account {{ $shoppingcart->shoppingcart_payment->va_number }}<br>5. Konfirmasi kode bank, nomor virtual account dan nominal pembayaran, jika sudah sesuai pilih "Benar"<br>6. Selesai<br>
@endif

@if($shoppingcart->shoppingcart_payment->bank_code=="009")
<center><h1 style="margin-top:2px;">How to Pay BNI Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">Mobile banking payment</h2>1. Buka aplikasi BNI Mobile<br>2. Login ke akun BNI Mobile Banking Anda.<br>3. Pilih menu pembayaran<br>4. Pilih "Transfer"<br>5. Pilih E-Commerce<br>6. Pilih "Virtual Account Billing", lalu pilih rekening debit Anda<br>7. Pilih merchant Budiyanto<br>8. Masukkan Nomor Virtual Account Anda di "Input Baru"<br>9. Masukkan nomor pembayaran {{ $shoppingcart->shoppingcart_payment->va_number }}<br>10. Konfirmasi bahwa jumlah pembayaran ditampilkan di layar<br>11. Masukkan PIN<br>12. Konfirmasi transaksi dan masukkan kata sandi transaksi Anda<br>13. Konfirmasi transaksi<br>14. Transaksi berhasil<br><h2 style="margin-bottom:0px;">ATM BNI</h2>1. Masukkan kartu ATM BNI, lalu masukkan PIN ATM.<br>2. Pilih menu "Menu Lain", lalu pilih menu "Transfer"<br>3. Pilih Jenis Akun<br>4. Pilih "Ke Rekening BNI"<br>5. Masukkan nomor rekening dengan Kode Pembayaran ({{ $shoppingcart->shoppingcart_payment->va_number }}) dan pilih "Benar"<br>6. Saat Konfirmasi Pembayaran muncul, pilih "Ya"<br>7. Transaksi sudah selesai, mohon simpan struknya.<br><h2 style="margin-bottom:0px;">BNI Internet Banking</h2>1. Login Internet Banking, lalu pilih menu "Transfer".<br>2. Pilih menu "In-House".<br>3. Masukkan nomor rekening dengan Kode Pembayaran Anda ({{ $shoppingcart->shoppingcart_payment->va_number }}) lalu pilih "Kirim".<br>4. Masukkan "Kata Sandi" dan OTP Anda.<br>5. Transaksi sudah selesai, mohon simpan struknya.<br><h2 style="margin-bottom:0px;">BNI Teller</h2>1. Kunjungi Teller Bank BNI di Kantor BNI.<br>2. Isi Formulir Setoran Tunai<br>3. Pilih "Tunai" atau tunai. Masukkan Kode Pembayaran ({{ $shoppingcart->shoppingcart_payment->va_number }}) dan jumlah. Tulis nama dan tanda tangan Anda.<br>4. Kirim Formulir Setoran Tunai dan uang tunai ke Teller BNI.<br>5. Transaksi selesai, mohon simpan salinan Formulir Setoran Tunai sebagai tanda terima pembayaran.<br><h2 style="margin-bottom:0px;">Other Bank ATM</h2>1. Masukkan kartu ATM, lalu masukkan PIN ATM.<br>2. Pilih menu "Transfer Antar Bank".<br>3. Masukkan "Kode Bank Tujuan" : BNI (Kode Bank : 009) + Kode Pembayaran ({{ $shoppingcart->shoppingcart_payment->bank_code }}{{ $shoppingcart->shoppingcart_payment->va_number }}).<br>4. Masukan "jumlah".<br>5. Saat Konfirmasi Transfer muncul, pilih "Ya" / "Lanjut"."<br>6. Transaksi sudah selesai, silahkan ambil struknya.<br>
@endif

@if($shoppingcart->shoppingcart_payment->bank_code=="002")
<center><h1 style="margin-top:2px;">How to Pay BRI Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">ATM BRI</h2>1. Masukkan Kartu Debit BRI dan PIN Anda<br>2. Pilih menu Transaksi Lain > Pembayaran > Lainnya > BRIVA<br>3. Masukkan kode pembayaran / virtual account number anda : {{ $shoppingcart->shoppingcart_payment->va_number }}<br>4. Di halaman konfirmasi, pastikan detil pembayaran sudah sesuai seperti Nomor BRIVA, Nama Pelanggan dan Jumlah Pembayaran<br>5. Ikuti instruksi untuk menyelesaikan transaksi<br>6. Simpan struk transaksi sebagai bukti pembayaran<br><h2 style="margin-bottom:0px;">Mobile Banking BRI</h2>1. Login aplikasi BRI Mobile<br>2. Pilih menu BRIVA<br>3. Pilih pembayaran baru<br>4. Masukkan kode pembayaran / virtual account number anda : {{ $shoppingcart->shoppingcart_payment->va_number }}<br>5. Di halaman konfirmasi, pastikan detil pembayaran sudah sesuai seperti Nomor BRIVA, Nama Pelanggan dan Total Pembayaran<br>6. Masukkan PIN<br>7. Simpan notifikasi SMS sebagai bukti pembayaran<br><h2 style="margin-bottom:0px;">Internet Banking BRI</h2>1. Login pada alamat Internet Banking BRI (https://ib.bri.co.id/ib-bri/Login.html)<br>2. Pilih menu Pembayaran Tagihan > Pembayaran > BRIVA<br>3. Masukkan kode pembayaran / virtual account number anda : {{ $shoppingcart->shoppingcart_payment->va_number }}<br>4. Di halaman konfirmasi, pastikan detil pembayaran sudah sesuai seperti Nomor BRIVA, Nama Pelanggan dan Jumlah Pembayaran<br>5. Masukkan password dan mToken<br>6. Cetak/simpan struk pembayaran BRIVA sebagai bukti pembayaran<br><h2 style="margin-bottom:0px;">Mini ATM/EDC BRI</h2>1. Pilih menu Mini ATM > Pembayaran > BRIVA<br>2. Swipe Kartu Debit BRI Anda<br>3. Masukkan kode pembayaran / virtual account number anda : {{ $shoppingcart->shoppingcart_payment->va_number }}<br>4. Masukkan PIN<br>5. Di halaman konfirmasi, pastikan detil pembayaran sudah sesuai seperti Nomor BRIVA, Nama Pelanggan dan Jumlah Pembayaran<br>6. Simpan struk transaksi sebagai bukti pembayaran<br><h2 style="margin-bottom:0px;">ATM Bank Lain</h2>1. Masukkan Kartu Debit dan PIN Anda<br>2. Pilih menu Transaksi Lainnya > Transfer > Ke Rek Bank Lain<br>3. Masukkan kode bank BRI kemudian diikuti kode pembayaran / virtual account number anda : {{ $shoppingcart->shoppingcart_payment->banl_code }}{{ $shoppingcart->shoppingcart_payment->va_number }}<br>4. Ikuti instruksi untuk menyelesaikan transaksi<br>5. Simpan struk transaksi sebagai bukti pembayaran<br>
@endif

@if($shoppingcart->shoppingcart_payment->bank_code=="213")
<center><h1 style="margin-top:2px;">How to Pay JENIUS (BTPN) Virtual Account</h1></center>
<h2 style="margin-bottom:0px;">Jenius App</h2>
1. Buka menu "Send It"<br>
2. Klik "Tambah Penerima"<br>
3. Pilih bank tujuan "BTPN"<br>
4. Masukkan nomor rekening virtual {{ $shoppingcart->shoppingcart_payment->va_number }} dan klik "Lanjut"<br>
5. Masukkan jumlah nominal<br>
6. Ikuti petunjuk hingga transaksi selesai<br>
<h2 style="margin-bottom:0px;">ATM BTPN</h2>
1. Masukkan kartu debit/ATM Anda<br>
2. Masukkan PIN Anda<br>
3. Pilih "Menu Lain"<br>
4. Pilih "Transfer"<br>
5. Pilih "Rekening Nasabah Lain di BTPN"<br>
6. Input nomor VA {{ $shoppingcart->shoppingcart_payment->va_number }} sebagai nomor rekening tujuan<br>
7. Periksa detil transaksi pada layar ATM<br>
8. Jika sudah sesuai, konfirmasi transaksi Anda<br>
9. Jika transaksi berhasil, Anda akan melihat tampilan "Transaksi berhasil" pada layar ATM Anda<br>
<h2 style="margin-bottom:0px;">Phone (Khusus Nasabah BTPN Wow!)</h2>
1. Telepon *247#<br>
2. Masukkan PIN BTPN Wow! Anda dan kirim<br>
3. Ketik 99 (untuk ke Menu Utama) dan kirim<br>
4. Ketik 4 (untuk ke menu Kirim Uang) dan kirim<br>
5. Ketik 4 (untuk ke menu Rekening Virtual) dan kirim<br>
6. Masukkan nomor tujuan rekening virtual {{ $shoppingcart->shoppingcart_payment->va_number }} dan kirim<br>
7. Masukkan jumlah pengiriman uang dan kirim<br>
8. Ketik PIN BTPN Wow! untuk konfirmasi transaksi<br>
9. Anda akan menerima notifikasi transaksi berhasil<br>
<h2 style="margin-bottom:0px;">Tunai melalui Agen (Khusus Nasabah BTPN Wow!)</h2>
1. Kunjungi Agen BTPN Wow! terdekat dengan membawa uang tunai yang akan dikirimkan<br>
2. Informasikan nomor rekening virtual tujuan {{ $shoppingcart->shoppingcart_payment->va_number }} dan nominal yang akan dikirimkan ke Agen BTPN Wow!<br>
3. Serahkan uang tunai ke Agen BTPN Wow!<br>
4. Agen BTPN Wow! akan memproses transaksi Anda melalui aplikasi/sistem agen BTPN Wow!<br>
5. Agen BTPN Wow! akan memperlihatkan bukti transaksi berhasil berupa SMS ataupun notifikasi pada aplikasi<br>
@endif

<div style="clear: both;"></div>
<footer>
	
</footer>
</body>
</html>