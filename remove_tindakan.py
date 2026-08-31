import os

file_path = 'local/api/models/Catatan.php'
with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Remove the left join
content = content.replace("LEFT JOIN prm_master_tindakan t ON c.id_tindakan = t.id\n", "")
content = content.replace("LEFT JOIN prm_master_tindakan t ON c.id_tindakan = t.id", "")

# Remove t.nama_tindakan, from SELECT
content = content.replace("c.*, t.nama_tindakan, p.nama as nama_paket", "c.*, p.nama as nama_paket")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("Removed prm_master_tindakan from Catatan.php!")
