<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function pageabout(){
?>
<div id="about">
<div class="logo">
	<img src="img/bigmantra.png"/>
</div>

<div class="about">
<ol>
<li><h1>DEFINISI DAN KONSEP DASAR</h1><br/>
	<p>
		Aplikasi <b>M A N T R A</b> merupakan perangkat lunak pendukung <b>Kerangka Kerja Interoperabilitas Sistem Informasi Elektronik</b> 
		dengan menerapkan teknologi Layanan Berbasis Web (Webservices) sebagai media pendukung Aplikasi Antarmuka/Perantara Akses Data Elektronik  
		dalam rangka melaksanakan pertukaran data atau berbagi pakai data antar Sistem Informasi Elektronik. 
		Webservices menjadi pilihan karena beroperasi dengan teknologi berbasis Standar Terbuka (Open Standard) dan dalam pengembangannya 
		harus menggunakan Perangkat Lunak dengan Kode Sumber Terbuka (Open Source Software).
		Pemanfaatan teknologi Webservices memberikan kemampuan Multi-Platform pada Aplikasi Perantara Akses Data Elektronik
		dalam penerapan Interoperabilitas Sistem Informasi Elektronik yang mencakup keragaman informasi dan format data.
	</p> 
	<p>
		Aplikasi Perantara Akses Data Elektronik yang berbasis Layanan Web umumnya dinamakan Antarmuka Program Aplikasi (Application Programming Interface/API)
		atau disingkat <b>Web-API</b>. Web-API bersifat reusable (dapat didaur ulang) tanpa merubah akses layanan (alamat dan atribut end point).
		Web-API digunakan sebagai akses terhadap suatu fungsi/prosedur pengolahan data dalam program aplikasi yang dikomunikasikan dari aplikasi lain
		yang berbeda platform dan lokasi bahkan dengan jarak yang berjauhan melalui jaringan internet umumnya dinamakan Remote Procedure Call (RPC) 
		atau dengan kata lain Web-API dapat mengakses sumberdaya layanan, program, informasi atau data dari tempat yang berbeda.
		Web-API dalam aplikasi MANTRA berfungsi menterjemahkan bentuk, struktur, dan semantik suatu sumber data ke dalam format data 
		standar yang dapat dibaca oleh semua Aplikasi berupa format data XML, JSON, PHP-ARRAY, PHP-SERIALIZE. 
	</p>
	<p>
		Komunikasi data melalui Web-API dalam Aplikasi MANTRA dapat dilakukan melalui beberapa model interkoneksi, diantaranya:
		<ul>
			<li>
				<p><b>SOAP (Simple Object Access Protocol)</b></p>
				<div class="picture">
					<img src="img/soap.png"/>
				</div>
				<p>
				Komunikasi data model SOAP dilakukan antara Aplikasi Client/Requester (SOAP-Client) dengan Web-API/Provider (SOAP-Server) 
				melalui alamat Web-API dengan protokol HTTP/s (Hyper Text Transfer Protocol/Secure). Informasi Metadata yang disediakan SOAP-Server 
				dapat disajikan melalui aplikasi Web-Browser dalam bentuk dokumen format XML dengan nama Web Services Description Language (WSDL), 
				sementara data permintaan (SOAP-Request) dan tanggapan (SOAP-Response) dilewatkan diantara SOAP-Client dan SOAP-Server dalam format 
				dokumen XML SOAP-Envelope yang dibentuk oleh fungsi SOAP-Server pada Web-API. 
				</p>
				<br/>
			</li>
			<li>
				<p><b>REST (REpresentational State Transfer)</b></p>
				<div class="picture">
					<img src="img/rest.png"/>
				</div>
				<p>
				Komunikasi model REST dilakukan antara Aplikasi Client/Requester dengan Web-API/Provider melalui Alamat Web-API dengan protokol HTTP/s 
				(Hyper Text Transfer Protocol/Secure). Informasi Metadata yang disediakan Web-API dapat disajikan melalui aplikasi Web-Browser dalam bentuk 
				dokumen format XML/HTML/JSON/CSV dengan nama Web Application Description Language (WADL), sementara data permintaan (Adapter-Request) dan 
				tanggapan (API-Response) dilewatkan diantara Aplikasi dan Web-API dalam format dokumen standar XML, JSON, RSS yang dibentuk oleh Web-API. 
				</p>
				<br/>
			</li>
		</ul>
	</p>
</li>

<li><h1>ARSITEKTUR</h1><br/>
	<p>
		Mekanisme berbagi pakai data melalui Web-API dinamakan mekanisme berbasis layanan API sehingga membentuk suatu Arsitektur Berbasis Layanan 
		yang disebut SOA (Service Oriented Architecture), beberapa bentuk arsitekturnya dapat dilakukan dengan ilustrasi sebagai berikut:
	</p>
	<div class="picture">
		<img src="img/ws.png"/>
	</div>
	<p>
		Arsitektur ini dinamakan model Point-to-Point antar Web-API. 
	</p>
	<div class="picture">
		<img src="img/int-p2p.png"/>
	</div>
	</p>
	<br/>
	<p>
		Interoperabilitas dengan Arsitektur Point-to-Point antar Web-API yang semakin banyak mengakibatkan sulitnya Requester mengatur pemanfaatan Web-API 
		berdasarkan Alamat Akses Web-API, sehingga perlu adanya Agen pengelola daftar layanan berbagi pakai data melalui Web-API yang disediakan Provider. 
		Sesuai dengan perannya Agen ini dinamakan Universal Description Discovery and Integration (UDDI). Dengan adanya Agen UDDI maka konsep Point-to-Point 
		dapat dilengkapi dengan Direktori Referensi Layanan. 
	</p>
	<div class="picture">
		<img src="img/soa.png"/>
	</div>
	<p>
		Tersedianya Agen Informasi Web-API pada konsep Agen UDDI, membentuk interaksi kompleks antar Web-API seperti ilustrasi berikut ini:
	</p>
	<div class="picture">
		<img src="img/int-soa.png"/>
	</div>
	<br/>
	<p>
		Interaksi Web-API menggunakan konsep Agen UDDI hanya dapat dilakukan pada satu Segmen/Sektor Agen saja. Agar dapat berkomunikasi antar Segmen/Sektor 
		maka dibutuhkan media koneksi antar Agen UDDI. Pola komunikasi antar Agen UDDI secara terintegrasi dapat dilakukan melalui suatu Kanal yang disebut Bus.
		Model Bus ini umumnya dinamakan Service Bus karena dianggap sebagai media perantara layanan Web-API yang tidak hanya memberikan informasi layanan berbagi pakai data 
		suatu Web-API akan tetapi memberikan fasilitas akses koneksi terhadap suatu Web-API melalui Adapter, dengan demikian dapat menjamin keamanan akses terhadap Web-API, 
		dan juga menjamin ketersediaan data/informasi dari Web-API melalui fungsi Cache Proxy.
	</p>
	<p>
		Dalam aplikasi MANTRA, media Service Bus ini dirancang dalam bentuk Arsitektur Interoperabilitas Terintegrasi antar Sistem Informasi Elektronik Badan Pemerintahan 
		dengan nama Government Service Bus (GSB) yang diilustrasikan pada gambaran sebagai berikut:
	</p>
	<br/>
	<div class="picture">
		<img src="img/arsitektur-gsb.png"/>
	</div>
	<br/>
	<p>
		Sedangkan Arsitektur dalam aplikasi MANTRA yang dapat berfungsi sebagai API Services (Data/Function) dan Bus Services (Proxy) diilustrasikan pada gambaran sebagai berikut:
	</p>
	<br/>
	<div class="picture">
		<img src="img/arch-mantra.png"/>
	</div>
	<br/>
	<p>
		Fitur-fitur yang dimiliki aplikasi MANTRA adalah sebagai berikut:
	</p>
	<br/>
	<div class="picture">
		<img src="img/fitur-mantra.png"/>
	</div>
	<br/>
	<p>
		Dengan adanya Service Bus (Kanal berbasis SOA), maka fungsi Agen UDDI dapat disatukan dalam layanan akses Service Bus.
		Bahkan Interaksi antara Requester dan Provider tidak lagi dilakukan secara langsung tetapi harus melalui pengaturan dalam Service Bus agar
		terjamin sistem keamanan Integrasi Informasi dan Pertukaran Data pada tiap layanan.
	</p>
	<br/>
	<div class="picture">
		<img src="img/int-gsb.png"/>
	</div>
	<br/>
	<p>
		Bila diperhatikan gambaran interaksi antar Web-API GSB seolah-olah akan mengalami penyempitan akses (bottle-neck) sehingga rentan terjadi dead-lock atau gagal proses.
		Secara fisik memang tampak seperti itu namun secara teknis akses melalui GSB sudah ditangani dengan baik
		melalui manajemen proses hyperthreading pada Web Server dan fasilitas proxy untuk mengurangi beban akses. 
		Informasi/data yang dihasilkan suatu Web-API tidak dianjurkan dalam jumlah baris/record yang besar 
		karena Web-API hanya difungsikan sebagai media referensi untuk validasi, verifikasi dan akurasi data saja.
		Dengan demikian Sinkronisasi Data dalam jumlah baris/record yang besar tidak direkomendasikan untuk diakses melalui Web-API atau GSB.
	</p>
	<p>
		Manfaat penggunaan Aplikasi MANTRA antara lain:
	</p>
	<table>
		<tr>
			<td>Efisiensi</td><td>:</td><td>Penghematan biaya pengembangan fitur Aplikasi Pengolah Data melalui pemanfaatan Web-API</td>
		</tr>
		<tr>
			<td>Efektifitas</td><td>:</td><td>Mengurangi duplikasi data maupun layanan Web-API dengan memanfaatkan Service Bus</td>
		</tr>
		<tr>
			<td>Reusability</td><td>:</td><td>Setiap layanan dapat dikembangkan lagi menjadi layanan baru dengan memanfaatkan layanan yang sudah ada, 
			sehingga pengembangan tidak perlu dibuat dari awal (from scratch)</td>
		</tr>
		<tr>
			<td>Akurat</td><td>:</td><td>Mempermudah penetapan proses validasi dan verifikasi data pada sumber yang tepat.</td>
		</tr>
		<tr>
			<td>Interoperable</td><td>:</td><td>Memiliki kemampuan berbagi data yang dilengkapi fasilitas Konversi Metadata (Ontologi Data).</td>
		</tr>
		<tr>
			<td>Futuristik</td><td>:</td><td>Menjadi komponen basis pengembangan teknologi modern seperti Cloud Services diantaranya untuk pengembangan Software as a Services (SaaS), Platform as a Services (PaaS), dll.</td>
		</tr>
	</table>
	<br/>
</li>

<li><h1>PROSEDUR PENGGUNAAN</h1><br/>
	<p>
		Kemampuan yang dimiliki Aplikasi MANTRA antara lain:
		<ul>
			<li>Memudahkan pembuatan Web-API secara interaktif melalui Graphics User Interface berbasis Web</li>
			<li>Mengatur akses interoperabilitas antara Web-API dengan Aplikasi Pemanfaatnya</li>
		</ul>
	</p>
	<p>
		Berdasarkan kemampuan yang dimiliki Aplikasi MANTRA yaitu sebagai Web-API maupun GSB,
		pengelolaannya dapat dilakukan melalui peran pengguna dan otorisasinya.
		Selain kemampuan tersebut, Aplikasi MANTRA dapat dikembangkan sesuai kebutuhan pemanfaatannya.
		Hal ini dikarenakan Aplikasi MANTRA dikembangkan dengan aplikasi berbasis Open Source Software 
		melalui dukungan perangkat lunak seperti Apache Web-Services, PHP, MySQL, Javascript, ADODB dan nuSOAP.
	</p>
	<p>
		Aplikasi MANTRA memiliki beberapa level/tingkatan peran pengguna berdasarkan instansi/penyedia layanan secara hirarki dari yang terendah sampai yang tertinggi sebagai berikut:
	</p>
	<ul>
		<li>Requester, berperan sebagai pihak pengguna yang memanfaatkan Web-API yang disediakan oleh Aplikasi MANTRA</li>
		<li>Publisher, berperan sebagai pihak penyedia yang mendaftarkan alamat Web-API ke dalam GSB</li>
		<li>Provider, berperan sebagai pihak yang membuat Web-API</li>
		<li>Administrator, berperan sebagai pihak yang mengatur pendelegasian dan pengelolaan Aplikasi MANTRA</li>
		<li>Supervisor, berperan sebagai pihak yang mengendalikan pengaturan layanan dalam Aplikasi MANTRA</li>
	</ul>
	<br/>
	<p>
		Tahap pelaksanaan Interoperabilitas menggunakan aplikasi MANTRA digambarkan dengan diagram berikut ini: 
	</p>
	<br/>
	<div class="picture">
		<img src="img/prosedur-mantra.png"/>
	</div>
	<br/>
	<p>
		<ol style="margin-left:2em;">
			<li>Administrator (Admin) pada Web-API bertugas mendelegasikan akses pengguna aplikasi MANTRA untuk Penyedia (Provider) dan Pemanfaat (Requester) Web-API</li>
			<li>Penyedia (Provider) pada Web-API membuat Fungsi Operasi Web-API melalui pendefinisian data/program dan elemennya</li>
			<li>Pemanfaat dari pihak penyedia (Requester) dalam hal ini yang terdaftar sebagai Publisher di GSB memesan Alamat Akses Fungsi Operasi dalam Web-API Provider yang akan didaftarkan ke GSB setelah disetujui Penyedia (Provider)</li>
			<li>Administrator (Admin) pada GSB bertugas mendelegasikan akses pengguna untuk Publisher dan Pemanfaat (Requester) Layanan GSB</li>
			<li>Pempublikasi (Publisher) mendaftarkan Alamat Akses Web-API Penyedia ke dalam GSB</li>
			<li>Pemanfaat dari pihak lain (Requester) memesan dan mengakses Layanan GSB setelah disetujui Publisher untuk unduh adapter ke dalam Aplikasi yang akan mengaksesnya</li>
		</ol>
	</p>
	<br/>
</li>

<li><h1>SPESIFIKASI</h1><br/>
	<ol>
		<li>
			<h1>Fitur Aplikasi MANTRA</h1>
			<table>
				<tr>
					<td>Platform Teknologi</td><td>:&nbsp;</td><td>Web Services</td>
				</tr>
				<tr>
					<td>Protokol Interkoneksi</td><td>:&nbsp;</td><td>HTTP/s</td>
				</tr>
				<tr>
					<td>Metode Interkoneksi</td><td>:&nbsp;</td><td>SOAP & REST</td>
				</tr>
				<tr>
					<td>Format Data/Dokumen</td><td>:&nbsp;</td><td>XML, JSON, PHP-Array, PHP-Serialize</td>
				</tr>
				<tr>
					<td>Kapabilitas</td><td>:&nbsp;</td><td>Data/Fuction Services & Proxy Services</td>
				</tr>
				<tr>
					<td>Dukungan DBMS</td><td>:&nbsp;</td><td>MySQL, PostgreSQL, MS-SQL/Sybase, ORACLE, DBASE/FOXPRO/MS-ACCESS (Windows).</td>
				</tr>
				<tr>
					<td>Dukungan Berkas</td><td>:&nbsp;</td><td>Semua Berkas Dokumen (CSV, PDF, JPG, PNG, BMP, DOC, XLS, dll.)</td>
				</tr>
				<tr>
					<td>Akses Antarmuka</td><td>:&nbsp;</td><td>Web-GUI</td>
				</tr>
				<tr>
					<td>Pengembangan</td><td>:&nbsp;</td><td>Open Source Code</td>
				</tr>
				<tr>
					<td>Standar Aplikasi</td><td>:&nbsp;</td><td>Web Base</td>
				</tr>
			</table>
				<!--ul>
					<li>Menggunakan Teknologi Web Services dengan protokol HTTP/s yang mendukung Multi-platform.</li>	
					<li>Beroperasi pada berbagai Sistem Operasi yang mendukung Teknologi Web Services.</li>	
					<li>Mendukung model interkoneksi Web Services dengan SOAP dan REST.</li>
					<li>Format data/dokumen yang digunakan adalah XML, JSON, PHP-ARRAY, dan PHP-Serialize.</li>
					<li>Menyediakan Akses Layanan Data/Fungsi (Data/Program Services) dan Akses Antar Layanan Web-API (Proxy Services).</li>
					<li>Layanan data yang mendukung DBMS seperti MySQL, PostgreSQL, MS-SQL/Sybase, ORACLE, DBASE/FOXPRO, CSV, serta dokumen PDF, JPG, PNG, DOC, XLS, dll</li>
					<li>Memudahkan pengelolaan Web-API dengan antarmuka pengguna berbasis GUI.</li>
					<li>Memberikan peluang pengembangan pemrograman berbasis Kode Sumber Terbuka (Open Source Code)</li>
					<li>Memenuhi standar aplikasi berbasis Web.
				</ul-->
			<br/>
		</li>
		<li>
			<h1>Perangkat Lunak (Software) pendukung (Minimal)</h1>
			<table>
				<tr>
					<td>Web Services</td><td>:&nbsp;</td><td>Apache versi 2.x</td>
				</tr>
				<tr>
					<td>Web Preprocessing</td><td>:&nbsp;</td><td>PHP versi 5.x</td>
				</tr>
				<tr>
					<td>Database Management</td><td>:&nbsp;</td><td>MySQL versi 5.x</td>
				</tr>
				<tr>
					<td>Operating System</td><td>:&nbsp;</td><td>Linux, Unix, Windows</td>
				</tr>
			</table>
			<br/>
		</li>
		<li>
			<h1>Perangkat Keras (Hardware) dan Akses pendukung (Minimal)</h1>
			<table>
				<tr>
					<td>Processor</td><td>:&nbsp;</td><td>1GHz Hyperthreading</td>
				</tr>
				<tr>
					<td>RAM</td><td>:&nbsp;</td><td>8 GByte</td>
				</tr>
				<tr>
					<td>Storage</td><td>:&nbsp;</td><td>500 GByte</td>
				</tr>
				<tr>
					<td>NIC</td><td>:&nbsp;</td><td>100 Mbps</td>
				</tr>
				<tr>
					<td>Bandwidth</td><td>:&nbsp;</td><td>10 Mbps</td>
				</tr>
			</table>
			<br/>
		</li>
	</ol>
	<br/>
</li>

</ol>
</div>
</div>
<?php
}

