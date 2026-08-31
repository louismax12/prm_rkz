import os

files_to_fix = [
    'local/api/controllers/AuditController.php',
    'local/api/controllers/AuthController.php',
    'local/api/controllers/KasirController.php',
    'local/api/controllers/PaketController.php'
]

for file_path in files_to_fix:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replace `"]);` with `"));` because the closing bracket of the array is before `);`
    content = content.replace('"]);', '"));')
    
    # Also for single quotes `']);` with `'));`
    content = content.replace("']);", "'));")
    
    # What about `$debugMsg]);` ?
    content = content.replace('$debugMsg]);', '$debugMsg));')
    content = content.replace('$username . "\'"]);', '$username . "\'"));')
    
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)

print("Fixed closing brackets with replace!")
