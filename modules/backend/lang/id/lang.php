<?php return [
  'auth' => [],
  'field' => [
    'invalid_type' => 'Jenis bidang isian tidak valid karena menggunakan :type.',
    'options_method_not_exists' => 'Kelas model :model harus menentukan metode :method() yang mengembalikan opsi untuk bidang isian \':field\'.',
  ],
  'widget' => [
    'not_registered' => 'Widget bernama \':name\' belum terdaftar',
    'not_bound' => 'Widget dengan kelas bernama \':name\' belum terikat pada controller',
  ],
  'page' => [
    'untitled' => 'Tidak ada judul',
    'access_denied' => [
      'label' => 'Akses ditolak',
      'help' => 'Anda tidak memiliki izin untuk melihat halaman ini.',
      'cms_link' => 'Kembali ke back-end',
    ],
  ],
  'partial' => [
    'not_found' => 'Potongan halaman \':name\' tidak ditemukan.',
  ],
  'account' => [
    'sign_out' => 'Keluar',
    'login_placeholder' => 'Nama Pengguna',
    'password_placeholder' => 'Kata Sandi',
    'enter_email' => 'Masukan email Anda',
    'email_placeholder' => 'Email',
    'apply' => 'Terapkan',
    'cancel' => 'Batalkan',
    'delete' => 'Hapus',
    'ok' => 'OK',
  ],
  'dashboard' => [
    'menu_label' => 'Dasbor',
    'widget_inspector_title' => 'Konfigurasi widget',
    'widget_inspector_description' => 'Konfigurasi laporan widget',
    'widget_columns_label' => 'Lebar :columns',
    'widget_columns_description' => 'Lebar widget, di antara angka 1 sampai 10.',
    'widget_columns_error' => 'Silakan masukan angka lebar widget di antara 1 sampai 10.',
    'columns' => '{1} kolom|[2,Inf] kolom',
    'widget_new_row_label' => 'Paksa untuk membuat baris baru',
    'widget_new_row_description' => 'Letakkan widget pada baris baru.',
    'widget_title_label' => 'Titel widget',
    'widget_title_error' => 'Titel widget diperlukan.',
    'status' => [
      'widget_title_default' => 'Status sistem',
      'update_available' => '{0} pembaruan tersedia!|{1} pembaruan tersedia!|[2,Inf] pembaruan tersedia!',
    ],
  ],
  'user' => [
    'name' => 'Administrator',
    'list_title' => 'Kelola Administrator',
    'new' => 'Administrator Baru',
    'first_name' => 'Name Depan',
    'last_name' => 'Name Belakang',
    'full_name' => 'Nama Lengkap',
    'email' => 'Email',
    'groups' => 'Grup',
    'groups_comment' => 'Tentukan grup apa yang harus dimiliki pengguna ini.',
    'avatar' => 'Avatar',
    'password' => 'Kata Sandi',
    'password_confirmation' => 'Konfirmasi kata sandi',
    'permissions' => 'Izin',
    'superuser' => 'Super User',
    'superuser_comment' => 'Checkbox ini untuk memperbolehkan pengguna agar dapat mengakses semua area.',
    'send_invite' => 'Kirim undangan lewat email',
    'send_invite_comment' => 'Gunakan checkbox untuk mengirimkan undangan ke pengguna lewat email',
    'delete_confirm' => 'Apakah Anda yakin akan menghapus administrator ini?',
    'return' => 'Kembali ke menu administrator',
    'allow' => 'Boleh',
    'inherit' => 'Mewarisi',
    'deny' => 'Tolak',
    'group' => [
      'name' => 'Grup',
      'name_field' => 'Nama',
      'description_field' => 'Deskripsi',
      'is_new_user_default_field' => 'Tambahkan administrator baru pada grup ini secara original',
      'code_field' => 'Kode',
      'code_comment' => 'Masukkan kode unik jika Anda ingin mengakses ini dengan API.',
      'list_title' => 'Kelola Grup',
      'new' => 'Grup Administrator Baru',
      'delete_confirm' => 'Apakah Anda yakin akan menghapus grup administrator ini?',
      'return' => 'Kembali ke menu grup',
    ],
    'preferences' => [
      'not_authenticated' => 'Tidak ada pengguna dengan autentikasi untuk mengatur atau menyimpan pengaturan.',
    ],
  ],
  'list' => [
    'default_title' => 'Tabel',
    'search_prompt' => 'Pencarian...',
    'no_records' => 'Tidak ada data dalam tampilan ini.',
    'missing_model' => 'Tidak ada Behavior Model di dalam :class.',
    'missing_column' => 'Tidak mendefinisikan sebuah kolom :columns.',
    'missing_columns' => 'Tabel yang digunakan dalam :class belum memiliki kolom.',
    'missing_definition' => 'Behavior Tabel tidak mengandung sebuah kolom \':field\'.',
    'behavior_not_ready' => 'Behavior Tabel belum diinisialisasi, periksa apakah Anda telah menggunakan makeLists() di dalam controller Anda.',
    'invalid_column_datetime' => 'Nilai kolom \':column\' bukan objek DateTime, apakah Anda lupa menggunakan $dates dalam Model?',
    'pagination' => 'Menampilkan data: :from s/d :to dari :total',
    'prev_page' => 'Sebelumnya',
    'next_page' => 'Berikutnya',
    'loading' => 'Memuat...',
    'setup_title' => 'Pengaturan Tabel',
    'setup_help' => 'Gunakan checkbox untuk memilih kolom yang ingin ditampilkan pada tabel. Anda dapat mengubah posisi kolom dengan cara menarik ke atas ke bawah.',
    'records_per_page' => 'Data per halaman',
    'records_per_page_help' => 'Pilih jumlah data per halaman untuk ditampilkan. Mohon diingat, jumlah data yang banyak dalam satu halaman dapat menurunkan performa.',
    'delete_selected' => 'Hapus yang dipilih',
    'delete_selected_empty' => 'Tidak ada data yang dipilih untuk dihapus.',
    'delete_selected_confirm' => 'Hapus data yang dipilih?',
    'delete_selected_success' => 'Berhasil menghapus data yang dipilih.',
  ],
  'fileupload' => [],
  'form' => [
    'create_title' => ':name Baru',
    'update_title' => 'Ubah :name',
    'preview_title' => 'Tampilan :name',
    'missing_id' => 'Form record ID belum ditentukan.',
    'missing_model' => 'Form Behavior yang digunakan dalam :class belum mendefinisikan model.',
    'missing_definition' => 'Form Behavior tidak berisi bidang isian untuk \':field\'.',
    'action_confirm' => 'Apakah Anda yakin?',
    'create' => 'Buat',
    'create_and_close' => 'Buat dan tutup',
    'creating' => 'Membuat...',
    'creating_name' => 'Membuat :name...',
    'save' => 'Simpan',
    'save_and_close' => 'Simpan dan tutup',
    'saving' => 'Menyimpan...',
    'saving_name' => 'Menyimpan :name...',
    'delete' => 'Menghapus',
    'deleting' => 'Menghapus...',
    'deleting_name' => 'Menghapus :name...',
    'reset_default' => 'Reset kembali ke awal',
    'resetting' => 'Pengaturan ulang',
    'resetting_name' => 'Pengaturan ulang :name',
    'undefined_tab' => 'Lain-lain',
    'field_off' => 'Off',
    'field_on' => 'On',
    'add' => 'Tambah',
    'apply' => 'Terapkan',
    'cancel' => 'Batal',
    'close' => 'Tutup',
    'confirm' => 'Memastikan',
    'reload' => 'Muat ulang',
    'ok' => 'OK',
    'or' => 'atau',
    'confirm_tab_close' => 'Apakah Anda yakin akan menutup tab? Perubahan yang belum tersimpan akan menghilang.',
    'behavior_not_ready' => 'Form Behavior belum terinisialisasi, periksa apakah Anda telah menggunakan initForm() pada controller Anda.',
    'preview_no_files_message' => 'Tidak ada berkas yang diunggah',
    'select' => 'Pilih',
    'select_all' => 'Pilih Semua',
    'select_none' => 'Pilih tidak ada',
    'insert_row' => 'Buat Baris',
    'delete_row' => 'Hapus Baris',
    'concurrency_file_changed_title' => 'Berkas telah diubah',
    'concurrency_file_changed_description' => 'Berkas yang Anda sunting telah diubah di dalam penyimpanan oleh pengguna lain. Anda dapat memuat ulang berkas dan merelakan perubahan yang telah Anda buat atau menimpa berkas pada penyimpanan.',
  ],
  'relation' => [
    'missing_config' => 'Relation Behavior tidak memiliki pengaturan untuk \':config\'.',
    'missing_definition' => 'Relation Behavior tidak berisi tentuan untuk \':field\'.',
    'missing_model' => 'Relation Behavior yang digunakan dalam :class belum mendefinisikan model.',
    'invalid_action_single' => 'Aksi ini tidak dapat dilakukan di dalam relasi tunggal.',
    'invalid_action_multi' => 'Aksi ini tidak dapat dilakukan di dalam relasi jamak.',
    'help' => 'Klik pada item untuk menambah',
    'add' => 'Tambah',
    'add_selected' => 'Tambah terpilih',
    'link_selected' => 'Tautan terpilih',
    'cancel' => 'Batal',
    'close' => 'Tutup',
    'create' => 'Buat',
    'update' => 'Perbarui',
    'preview' => 'Preview',
    'remove_name' => 'Lepas :name',
    'delete_name' => 'Hapus :name',
    'link' => 'Tautan',
    'unlink_name' => 'Buka Tautan :name',
  ],
  'model' => [
    'name' => 'Model',
    'not_found' => 'Model \':class\' dengan ID :id tidak dapat ditemukan',
    'missing_id' => 'Tidak ada ID yang spesifik untuk mencari data model.',
    'missing_relation' => 'Model \':class\' tidak mengandung relasi dengan \':relation\'.',
    'missing_method' => 'Model \':class\' tidak menggunakan metode \':method\'.',
    'invalid_class' => 'Model :model yang digunakan pada :class tidak valid, model haruslah menggunakan turunan kelas \\Model.',
    'mass_assignment_failed' => 'Keselarasan masal gagal untuk atribut Model \':attribute\'.',
  ],
  'warnings' => [
    'tips' => 'Tips pengaturan sistem',
    'tips_description' => 'Ada isu yang harus Anda perhatikan untuk mengatur sistem dengan tepat.',
    'permissions' => 'Direktori :name atau direktori di bawahnya tidak dapat ditulis oleh PHP. Silakan atur hak akses webserver yang sesuai pada direktori ini.',
    'extension' => 'Ekstensi PHP :name tidak terpasang. Silakan instalasi modul ini dan aktifkan ekstensi.',
  ],
  'editor' => [],
  'tooltips' => [],
  'mysettings' => [
    'menu_label' => 'Pengaturanku',
    'menu_description' => 'Pengaturan yang berkaitan dengan akun administrasi Anda',
  ],
  'myaccount' => [],
  'branding' => [
    'accent_color' => 'Warna Aksen',
  ],
  'backend_preferences' => [],
  'access_log' => [],
  'filter' => [
    'all' => 'semua',
  ],
];
