# Security Incident Remediation - Google Maps API Key Exposure

**Date**: November 17, 2025  
**Incident**: Google Maps API Key exposed in GitHub repository  
**Detected by**: GitGuardian  
**Affected Commit**: `ff5bc90`  
**Status**: 🟡 IN PROGRESS

---

## ✅ Step 1: Revoke the Exposed API Key (COMPLETED)

You've already regenerated a new API key - **Well done!** This makes the exposed key unusable.

### Verify Revocation in Google Cloud Console

1. Go to: https://console.cloud.google.com/apis/credentials
2. Find the **OLD** exposed API key
3. Click **Delete** or verify it's already deleted
4. Confirm only your **NEW** key exists

---

## ✅ Step 2: Update Local Environment with New Key

### Update Your `.env` File

```bash
cd /home/dennis-muchai/KejaLink
```

Edit `.env` and update the Google Maps API key:

```bash
# Replace with your NEW API key
VITE_GOOGLE_MAPS_API_KEY=your_new_api_key_here
```

**Verify the file is ignored by Git:**

```bash
git status
# .env should NOT appear in the list
```

---

## ✅ Step 3: Restrict Your New API Key

**CRITICAL**: Restrict the new key so even if exposed, it can't be abused.

### In Google Cloud Console:

1. **Go to**: https://console.cloud.google.com/apis/credentials
2. **Click** on your NEW API key
3. **Application restrictions**:
   - Select: "HTTP referrers (web sites)"
   - Add these referrers:
     ```
     https://kejalink.co.ke/*
     https://*.kejalink.co.ke/*
     http://localhost:*/*
     http://localhost:5173/*
     http://localhost:5174/*
     ```
4. **API restrictions**:
   - Select: "Restrict key"
   - Enable ONLY these APIs:
     - ✅ Maps JavaScript API
     - ✅ Places API
     - ✅ Geocoding API (optional)
   - Disable all others
5. **Click** "Save"

This ensures the key only works on your domains even if leaked.

---

## ✅ Step 4: Rebuild and Redeploy with New Key

### Build Frontend with New Key

```bash
cd /home/dennis-muchai/KejaLink

# Verify .env has new key
cat .env | grep VITE_GOOGLE_MAPS_API_KEY

# Build frontend (embeds new key)
npm run build

# Create deployment package
./create-deployment-package.sh
```

### Deploy to Production

1. **Login to cPanel**: https://kejalink.co.ke:2083
2. **Backup current production**:
   - File Manager → `/public_html/`
   - Right-click → Compress → `backup-before-key-rotation-20251117.zip`
3. **Upload new build**:
   - Upload latest `.zip` from `kejalink-frontend-*.zip`
   - Extract to `/public_html/`
4. **Test the site**:
   - Visit: https://kejalink.co.ke
   - Test autocomplete: Should work with NEW key
   - Check browser console: No API key errors

---

## ⚠️ Step 5: Clean Git History (CRITICAL)

**Problem**: The exposed key is still in your Git history. Anyone can see it in past commits.

### Option A: BFG Repo-Cleaner (Recommended)

```bash
cd /home/dennis-muchai/KejaLink

# Download BFG (if not installed)
wget https://repo1.maven.org/maven2/com/madgag/bfg/1.14.0/bfg-1.14.0.jar

# Create a backup first
git clone --mirror /home/dennis-muchai/KejaLink /tmp/kejalink-backup.git

# Replace exposed key in all commits
# Replace 'OLD_API_KEY' with your actual exposed key
java -jar bfg-1.14.0.jar --replace-text <(echo "AIzaSy***YOUR_OLD_KEY***==>REMOVED") .git

# Clean up
git reflog expire --expire=now --all
git gc --prune=now --aggressive

# Force push to GitHub (DANGER: This rewrites history)
git push --force --all
```

### Option B: Git Filter-Repo (Alternative)

```bash
# Install git-filter-repo
pip install git-filter-repo

# Create backup
cp -r /home/dennis-muchai/KejaLink /tmp/kejalink-backup

cd /home/dennis-muchai/KejaLink

# Remove the exposed key from all commits
git filter-repo --replace-text <(echo "AIzaSy***YOUR_OLD_KEY***==>REMOVED")

# Force push
git push --force --all
```

### Option C: Make Repository Private (Temporary Solution)

If you can't clean history immediately:

1. Go to: https://github.com/dmuchai/KejaLink/settings
2. Scroll to "Danger Zone"
3. Click "Change repository visibility"
4. Select "Make private"
5. Confirm

**Note**: This doesn't remove the exposed key from history, just hides it.

---

## ✅ Step 6: Prevent Future Incidents

### 1. Verify .gitignore

```bash
cd /home/dennis-muchai/KejaLink
cat .gitignore | grep -E '\.env|config\.php'
```

Should include:
```
.env
.env.local
.env.*.local
php-backend/config.php
php-backend/config.local.php
php-backend/email-config.php
```

✅ Already configured correctly!

### 2. Install Git Secrets Scanner (Optional)

```bash
# Install gitleaks
brew install gitleaks  # Mac
# or
wget https://github.com/gitleaks/gitleaks/releases/download/v8.18.0/gitleaks_8.18.0_linux_x64.tar.gz
tar -xzf gitleaks_8.18.0_linux_x64.tar.gz
sudo mv gitleaks /usr/local/bin/

# Scan before committing
gitleaks detect --source . --verbose
```

### 3. Add Pre-commit Hook

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash
# Check for potential secrets before commit

if gitleaks detect --source . --verbose --no-git 2>&1 | grep -q "leaks found"; then
    echo "❌ Potential secrets detected! Commit blocked."
    echo "Run: gitleaks detect --source . --verbose"
    exit 1
fi
```

Make executable:
```bash
chmod +x .git/hooks/pre-commit
```

### 4. Use Environment-Specific Files

**Never commit these files:**
- `.env`
- `.env.local`
- `config.php`
- `email-config.php`

**Safe to commit (templates only):**
- `.env.example`
- `.env.local.example`
- `config.example.php`

---

## 📋 Remediation Checklist

- [x] **Revoked exposed API key** (regenerated new key)
- [x] **Updated local `.env`** with new key
- [ ] **Restricted new key** in Google Cloud Console
- [ ] **Rebuilt frontend** with new key
- [ ] **Deployed to production** and tested
- [ ] **Cleaned Git history** (removed exposed key from commits)
- [ ] **Verified `.gitignore`** protects sensitive files
- [ ] **Installed secret scanner** (optional but recommended)
- [ ] **Updated team documentation** on secret management

---

## 🔍 Verify Remediation Complete

### 1. Check Production Site

```bash
# Visit site and check console
curl -s https://kejalink.co.ke | grep -o 'maps.googleapis.com/maps/api/js?key=[^"]*'
# Should show NEW key, not old one
```

### 2. Check Git History

```bash
cd /home/dennis-muchai/KejaLink
git log -p | grep -i "AIzaSy" | head -20
# Should NOT show your old exposed key
```

### 3. Test API Key Restrictions

```bash
# Try using key from different domain (should fail)
curl "https://maps.googleapis.com/maps/api/js?key=YOUR_NEW_KEY"
# Should return error if referrer restrictions work
```

---

## 📞 Support Resources

**GitGuardian**:
- Dashboard: https://dashboard.gitguardian.com
- Mark incident as resolved after cleaning history

**Google Cloud Console**:
- Credentials: https://console.cloud.google.com/apis/credentials
- Quotas: https://console.cloud.google.com/apis/api/maps-backend.googleapis.com/quotas

**GitHub Security**:
- Settings → Security → Secret scanning alerts

---

## 🎯 Final Status

Once all checklist items are complete:

- [ ] Incident remediated
- [ ] Production updated with new key
- [ ] Git history cleaned
- [ ] Restrictions applied
- [ ] Team notified
- [ ] Documentation updated

**Remediated by**: Dennis Muchai  
**Date**: November 17, 2025  
**Time**: _________  
**Final Status**: 🟡 In Progress / ✅ Complete

---

## 📝 Lessons Learned

1. **Never commit secrets** - Use .env files and .gitignore
2. **Always restrict API keys** - Limit by domain and APIs
3. **Monitor for leaks** - Use GitGuardian or similar tools
4. **Rotate keys regularly** - Every 90 days for sensitive keys
5. **Clean history immediately** - Don't just delete in latest commit

---

**Next Review**: 90 days from now (February 17, 2026)  
**Action**: Rotate Google Maps API key as preventive measure
