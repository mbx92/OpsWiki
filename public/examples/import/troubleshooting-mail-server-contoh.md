# Troubleshooting: Akses Mail Server Gagal dari VLAN Tertentu

## 1. Ringkasan kasus

Beberapa komputer di jaringan internal tidak bisa mengakses mail server `mail.example.com` (49.128.178.234) pada port SMTP 587. Subnet lain bisa akses normal. Setelah pengecekan bertahap, masalah berasal dari **perbedaan IP public hasil src-nat** — mail server hanya mengizinkan satu IP public sumber.

## 2. Gejala yang terlihat

### Subnet yang berhasil

```text
Client      : 10.5.80.141
Gateway     : 10.5.80.1
Mail server : 49.128.178.234:587
Status      : Berhasil
```

Connection tracking menunjukkan reply diterima (`repl-packets > 0`).

### Subnet yang gagal

```text
Client      : 10.5.60.60
Gateway     : 10.5.60.1
Mail server : 49.128.178.234:587
Status      : Gagal — TcpTestSucceeded: False
```

Connection tracking: `tcp-state=syn-sent`, `repl-packets=0` — tidak ada balasan dari mail server.

## 3. Root cause

Rule NAT MikroTik menggunakan pool IP public:

```mikrotik
chain=srcnat action=src-nat to-addresses=202.46.153.192/29 dst-address-list=!rfc1918
```

Akibatnya VLAN berbeda keluar dengan IP public berbeda:

```text
10.5.80.141 -> keluar sebagai 202.46.153.192 -> berhasil
10.5.60.60  -> keluar sebagai 202.46.153.194 -> gagal
```

Mail server / firewall tujuan hanya whitelist `202.46.153.192`.

## 4. Kenapa masalah ini terjadi

Pool NAT `/29` dibuat untuk membagi koneksi keluar ke beberapa IP public. Namun layanan eksternal yang memakai whitelist IP akan menolak koneksi dari IP lain dalam pool yang sama.

## 6. Solusi yang disarankan

Tambahkan rule src-nat khusus agar semua traffic internal ke mail server selalu keluar dari IP yang diizinkan:

```mikrotik
/ip firewall nat add chain=srcnat \
    src-address=10.5.0.0/16 \
    dst-address=49.128.178.234 \
    protocol=tcp \
    dst-port=25,465,587,993,995 \
    action=src-nat \
    to-addresses=202.46.153.192 \
    place-before=2 \
    comment="FIX mail via allowed public IP"
```

Hapus koneksi lama lalu test ulang:

```mikrotik
/ip firewall connection remove [find dst-address~"49.128.178.234"]
```

```powershell
Test-NetConnection mail.example.com -Port 587
```

## 7. Solusi jangka panjang

- Gunakan **satu IP public stabil** untuk NAT client utama, atau
- Whitelist seluruh blok public kantor di sisi mail server, atau
- Jangan pakai `/29` mentah sebagai `to-addresses` tanpa konfirmasi provider

## 8. Checklist verifikasi

Dari client:

```powershell
Test-NetConnection mail.example.com -Port 587
Test-NetConnection mail.example.com -Port 993
```

Dari MikroTik — pastikan `reply-dst-address` memakai IP allow dan `repl-packets > 0`.

## 9. Kesimpulan akhir

Masalah bukan DNS atau VLAN putus, melainkan **src-nat pool** yang membuat subnet keluar dengan IP public berbeda. Perbaikan aman: rule NAT khusus untuk mail server ke IP yang sudah di-whitelist.
