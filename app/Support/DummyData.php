<?php

namespace App\Support;

/** Data awal untuk seeder (judul, sampul, dan teks mengikuti desain Figma). */
class DummyData
{
    public static function categories(): array
    {
        return ['Romance', 'Fantasy', 'History', 'Mystery', 'Self Growth', 'Horror'];
    }

    public static function books(): array
    {
        $none = 'Sinopsis belum tersedia.';
        // judul, penulis, kategori, tags, file sampul, total, tersedia, rak, rating, sinopsis
        $rows = [
            ['What It Takes', 'Gita Wirjawan', 'History', 'Sosial,Sejarah', 'what-it-takes', 5, 3, 'C-1', 5.5, 'What It Takes: Asia Tenggara-Dari Tepi Menuju Inti Kesadaran Global menelaah modernisasi dan peran Asia Tenggara, rumah bagi 700 juta jiwa, di panggung global. Gita Wirjawan menyoroti beragam tantangan-seperti rendahnya PDB per kapita, kesenjangan pendidikan, dan dilema keberlanjutan seraya menegaskan keunggulan strategis yang dimiliki kawasan ini dalam tatanan dunia multipolar.'],
            ['Laut Bercerita', 'Leila S. Chudori', 'History', 'Fiksi,Sejarah', 'laut-bercerita', 5, 3, 'A-2', 5.5, 'Laut Bercerita, novel terbaru Leila S. Chudori, bertutur tentang kisah keluarga yang kehilangan, sekumpulan sahabat yang merasakan kekosongan di dada, sekelompok orang yang gemar menyiksa dan lancar berkhianat, sejumlah keluarga yang mencari kejelasan makam anaknya, dan tentang cinta yang tak akan luntur.'],
            ['Introvert on the Job', 'Johnny Djohari', 'Self Growth', 'Karier,Psikologi', 'introvert-on-the-job', 4, 2, 'E-1', 4.5, 'Bagaimana bisa membaur di tim, dia kan introver, Koordinasi pasti terhambat kalau dia yang memimpin, Jangan rekrut calon karyawan itu, dia introver.'],
            ["Man's Search for Meaning", 'Viktor E. Frankl', 'Self Growth', 'Psikologi,Filsafat', 'mans-search', 4, 1, 'E-2', 4.5, 'Buku Mans Search For Meaning karya Viktor E. Frankl membahas tentang pengalaman Viktor Frankl berada di empat kamp kematian Nazi yang berbeda, disitulah dia bertahan hidup ditengah tentara Nazi, namun istri dan saudara laki-lakinya tewas di dalam kamp tersebut.'],
            ['Tarot Scammer', 'Shannin', 'Romance', 'Komik,Romance', 'tarot-scammer', 4, 2, 'B-4', 4.5, 'Seumur hidupnya, Amadeo tidak pernah benar-benar mengambil keputusan sendiri, bahkan untuk pakaian yang ia kenakan. Sampai ia menemukan tarot dan menjadikannya tempat bertanya untuk hampir segala hal. Oleh karena itu, ketika seorang tarot reader misterius meramalkan bahwa ia akan bertemu seseorang yang mampu mengubah hidupnya, Amadeo memilih untuk percaya. Anehnya, setiap ramalan yang ia terima benar-benar terjadi.'],
            ['The Full Moon Eye Clinic', 'Byun yoonha', 'Fantasy', 'Fiksi,Fantasi', 'full-moon', 3, 0, 'B-5', 4.5, 'Berawal dari kejadian tak terduga, Eunhoo mendapati dirinya terlempar ke Klinik Mata Bulan Purnama—sebuah tempat ganjil yang lebih menyerupai dongeng kelam daripada fasilitas medis. Terdesak kebutuhan hidup, ia bertahan untuk bekerja di klinik itu, menyaksikan bagaimana emosi dan ingatan diperjualbelikan demi menyembuhkan "penyakit mata" yang berakar dari luka batin penderitanya.'],
            ['Taipan', 'William win yang', 'History', 'Fiksi,Bisnis', 'taipan', 4, 4, 'C-3', 4.5, 'Inilah kisah James King, putra Constantine King Sebuah kisah tentang si anak manja. Tentang cintanya yang terlarang. Tentang ambisinya yang menggebu. Tentang pembuktian dirinya demi menepis bayangan papinya, hingga rela masuk ke dunia mafia keuangan internasional. Tentang menjual negaranya demi sekeping ambisi.'],
            ['Teka-Teki Gambar Aneh', 'Uketsu', 'Mystery', 'Fiksi,Misteri', 'teka-teki', 6, 1, 'B-2', 4.5, 'Gambar yang dibuat seorang wanita hamil di internet... Gambar yang dibuat seorang anak kecil sebagai hadiah Hari Ibu... Gambar yang dibuat seorang korban pembunuhan di saat-saat terakhirnya...'],
            ['Tentang Ziarah yang Terkutuk', 'Sesuji', 'Horror', 'Fiksi,Horor', 'tentang-ziarah', 3, 3, 'B-6', 4.5, 'Kobayashi, seorang editor lepas, mengajukan sebuah proposal ke penerbit berupa rencana pembuatan fanbook untuk kanal Occult Yankee milik YouTuber penjelajah tempat angker, Chanike (alias Ikeda).'],
            ['Sang Alkemis', 'Paulo Coelho', 'Fantasy', 'Fiksi,Fantasi', 'sang-alkemis', 5, 2, 'D-1', 4.5, 'Setiap beberapa puluh tahun, muncul sebuah buku yang mengubah hidup para pembacanya selamanya. Novel Paulo Coelho yang memikat ini telah memberikan inspirasi bagi jutaan orang di seluruh dunia. Kisah yang sangat sederhana, namun menyimpan kebijaksanaan penuh makna, tentang anak gembala bernama Santiago yang berkelana dari rumahnya di Spanyol ke padang pasir Mesir untuk mencari harta karun terpendam di Piramida-Piramida.'],
            ['Lewat Tengah Malam', 'Sweta Kartika', 'Horror', 'Fiksi,Horor', 'lewat-tengah-malam', 3, 2, 'B-7', 4.5, 'Mereka bilang, harta bukanlah segalanya. Mereka juga bilang, tahta tak bisa membeli bahagia. Semua itu hanyalah dusta dari mulut-mulut nestapa. Harta dan tahta adalah segalanya. Nyawa pun layak ditumbalkan untuk mendapatkannya. Hingga akhirnya gerbang iblis pun terbuka.'],
            ['Rumah: Sebuah Novel', 'J.S. Khairen', 'Romance', 'Fiksi,Keluarga', 'rumah', 4, 4, 'A-5', 4.5, 'Ini kisah tentang rumah, tempat yang mengizinkanmu gagal berkali-kali. Di sini kau boleh lelah, kau boleh mengeluh. Di sini pula kau boleh salah, kau boleh marah. Bila perjalananmu sudah terlalu jauh dan melelahkan, pulanglah. Ambil selimut, menangislah. Ambil segelas teh hangat, teguklah. Bila kau sudah cukup kuat, kau boleh berkelana lagi. Dan rumahmu akan selalu setia menunggumu kembali.'],
            ['Hunter with a Scalpel', 'Choi Yi do', 'Mystery', 'Fiksi,Thriller', 'hunter-with-a-scalpel', 3, 1, 'B-3', 4.5, 'Seorang perempuan ditemukan tewas dengan tubuh yang dijahit menggunakan benang jahit biasa. Si Penjahit, begitu mereka menyebut si pelaku kejahatan. Bagi polisi, itu hanyalah petunjuk. Bagi Seo Se-hyun, dokter forensik jenius yang dikenal karena ketelitian dan instingnya, jahitan itu adalah mimpi buruk yang seharusnya telah terkubur bersama masa lalunya. '],
            ['Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'History', 'Fiksi,Sastra', 'ronggeng-dukuh-paruk', 4, 3, 'A-4', 4.5, 'Semangat Dukuh Paruk kembali menggeliat sejak Srintil dinobatkan menjadi ronggeng baru, menggantikan ronggeng terakhir yang mati dua belas tahun yang lalu. Bagi pedukuhan yang kecil, miskin, terpencil, dan bersahaja itu, ronggeng adalah perlambang. Tanpanya, dukuh itu merasa kehilangan jati diri. Dengan segera Srintil menjadi tokoh yang amat terkenal dan digandrungi. '],
        ];
        return array_map(fn ($r) => [
            'title' => $r[0], 'author' => $r[1], 'cat' => $r[2], 'tags' => $r[3], 'cover' => 'covers/' . $r[4] . '.jpg',
            'total' => $r[5], 'available' => $r[6], 'shelf' => 'Rak ' . $r[7], 'rating' => $r[8], 'synopsis' => $r[9],
        ], $rows);
    }
}
