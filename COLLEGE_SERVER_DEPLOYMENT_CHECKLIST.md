# PAILA College Server Deployment Checklist

This checklist matches the Herald College server guide. The college server is a shared PHP/MySQL server, not a Docker server.

## 1. Save Your Server Credentials

After registration, save the generated:

- SSH/SCP username
- SSH/SCP password
- MySQL username
- MySQL password

The guide says the MySQL database name and MySQL user are the same as your generated username.

## 2. Add Server-Only Config

Do not put real server passwords into GitHub.

On the server, copy:

```bash
cp config/db.local.example.php config/db.local.php
```

Then edit `config/db.local.php` with your generated college values:

- `DB_HOST`: usually `localhost`
- `DB_USER`: your generated MySQL username
- `DB_PASS`: your generated MySQL password
- `DB_NAME`: your generated MySQL database name

`BASE_URL` is auto-detected, so the site can run under:

- `http://student.heraldcollege.edu.np/~your_username/Paila/`
- `http://student.heraldcollege.edu.np/~your_username/paila-traveling-2461787/`
- or another folder name under `public_html`

## 3. Hero Videos

The server guide warns against uploading video files, but PAILA's homepage hero is designed around motion.

The current hero videos are each below the stated 50MB per-file limit. Upload `assets/video` if you want the full hero animation on the college server.

Important risk:

- Because this is a shared college server, staff may still remove or reject video-heavy projects even if each file is under 50MB.
- If that happens, the site will still fall back to image heroes because the code checks whether video files exist.

Before uploading, make a deployment copy that excludes only development/private files:

- `.git`
- `docker-compose.yml`
- `Paila_Deploy_Extras`
- `config/db.local.php`
- any backup `.sql` files outside `database/schema.sql` and `database/package_data.sql`

Keep:

- `assets/video`
- `assets/images`
- `public/uploads`

## 4. Upload Project Files

Create a clean deployment copy with videos, but without Git/Docker/private files:

```powershell
cd C:\Users\mount\OneDrive\Documents
Remove-Item .\Paila_College_Deploy -Recurse -Force -ErrorAction SilentlyContinue
robocopy .\Paila .\Paila_College_Deploy /E /XD .git Paila_Deploy_Extras /XF db.local.php
```

Then upload that clean folder:

```powershell
scp -r Paila_College_Deploy your_username@10.80.0.250:~/public_html/Paila
```

Then your site URL will be:

```text
http://student.heraldcollege.edu.np/~your_username/Paila/
```

If you rename the uploaded folder, use that folder name in the URL.

## 5. Import Database

SSH into the server:

```bash
ssh your_username@10.80.0.250
```

Go to the uploaded project folder:

```bash
cd ~/public_html/Paila
```

Import the schema:

```bash
mysql -u your_username -p your_username < database/schema.sql
```

Import package data:

```bash
mysql -u your_username -p your_username < database/package_data.sql
```

When prompted, enter your generated server password.

## 6. Fix Upload Permissions

Admin package image upload needs `public/uploads` to be writable.

Run:

```bash
chmod 755 public public/uploads
```

If image upload still fails, run:

```bash
chmod 775 public/uploads
```

## 7. Test These Pages

Open:

```text
http://student.heraldcollege.edu.np/~your_username/Paila/
http://student.heraldcollege.edu.np/~your_username/Paila/public/authentication/login.php
http://student.heraldcollege.edu.np/~your_username/Paila/admin/index.php
```

Admin login:

- Email: `2461787@paila.admin`
- Password: `PailaAdmin@2026`

## 8. If Something Breaks

Database error:

- Check `config/db.local.php`.
- Confirm database name, user, and password match your generated college credentials.

Blank images:

- Confirm `assets/images` uploaded.
- Confirm package image paths exist.

Admin upload error:

- Check `public/uploads` permissions.

404 on CSS or links:

- Confirm the project folder is inside `~/public_html`.
- Open the exact folder URL used during upload.
