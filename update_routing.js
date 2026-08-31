const fs = require('fs');
let code = fs.readFileSync('local/app.js', 'utf8');

// Replace `${API_BASE}/endpoint?` with `${API_BASE}?endpoint=endpoint&`
code = code.replace(/\$\{API_BASE\}\/([a-zA-Z0-9_-]+)\?/g, '\\${API_BASE}?endpoint=$1&');

// Replace `${API_BASE}/endpoint` with `${API_BASE}?endpoint=endpoint`
code = code.replace(/\$\{API_BASE\}\/([a-zA-Z0-9_-]+)/g, '\\${API_BASE}?endpoint=$1');

fs.writeFileSync('local/app.js', code);
console.log('Routing updated in app.js');
