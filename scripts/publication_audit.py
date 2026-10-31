#!/usr/bin/env python3
"""Auditoría del ÁRBOL actual; no inspecciona ni sanea el historial de Git."""
import pathlib
import re
import subprocess
import sys

root = pathlib.Path(__file__).resolve().parent.parent
result = subprocess.run(["git", "ls-files", "-z"], cwd=root, check=True, capture_output=True)
files = [pathlib.PurePosixPath(p.decode("utf-8")) for p in result.stdout.split(b"\0") if p]
issues = []

for path in files:
    name = path.name.lower()
    lower = str(path).lower()
    if (
        name in {".env", ".htpasswd", "db_config.local.php", "smtp_config.local.php"}
        or name.endswith((".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pfx", ".pem", ".key", ".sqlite", ".db"))
        or name.startswith("~$")
        or "/.vscode/" in f"/{lower}/"
        or lower.startswith("vendor/")
        or lower.startswith("avance2/vendor/")
        or lower.startswith(".git/")
        or re.fullmatch(r"\.env\..+", name)
    ):
        issues.append(f"Archivo no apto para publicación: {path}")

    if name == "db_config.php" and lower.startswith("avance2/"):
        code = (root / path).read_text(encoding="utf-8")
        if not re.search(r"db_config\.local\.php", code):
            issues.append("La conexión MySQL no utiliza configuración externa.")

expected = {
    "README.md",
    ".gitignore",
    "Avance2/db_config.example.php",
    "Avance2/smtp_config.example.php",
    "Avance2/security_rate_limit.php",
    "Avance2/.htaccess",
    ".htaccess",
    ".github/workflows/integration-smoke.yml",
}
missing = expected - {str(x) for x in files}
issues.extend(f"Falta el archivo obligatorio: {path}" for path in sorted(missing))

if issues:
    for issue in issues:
        print(f"[ERROR] {issue}")
    sys.exit(1)

print(f"[OK] Árbol actual: {len(files)} archivos controlados, sin archivos privados conocidos.")
print("[AVISO] La herramienta NO inspecciona el historial ni sustituye la rotación de credenciales.")
