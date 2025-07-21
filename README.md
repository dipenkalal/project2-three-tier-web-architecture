# AWS Three-Tier Web Architecture - Project Documentation

This repository contains a complete implementation of a **Three-Tier Web Architecture** on AWS, using EC2, RDS, VPC, subnets, NAT/IGW, and tiered security groups. This README provides an in-depth walkthrough of the setup.

---

## 🧱 Phase 1: Foundation (Networking Basics)

### 1. VPC

- Created a **custom VPC** (10.0.0.0/16)

### 2. Subnets

- **Public Subnets**: 10.0.1.0/24 (AZ1), 10.0.2.0/24 (AZ2)
- **Private Subnets**: 10.0.3.0/24 (AZ1), 10.0.4.0/24 (AZ2)

### 3. Internet Gateway (IGW)

- Created and attached to the custom VPC
- Associated with a route table for public subnets

### 4. Route Tables

- **Public RT**: Routes 0.0.0.0/0 to IGW
- **Private RT**: Routes 0.0.0.0/0 to NAT Gateway (for outbound internet access)

### 5. NAT Gateway

- Created in a public subnet
- Attached Elastic IP for internet access

---

## 🕸️ Phase 2: Networking & Security

### 6. Security Groups

- **Web SG**: Allows HTTP (80), SSH (22) from anywhere
- **App SG**: Allows HTTP from Web SG only
- **DB SG**: Allows MySQL (3306) from App SG only

### 7. NACLs

- Default NACLs used with inbound/outbound set to allow all (for simplicity)

### 8. Key Pair

- Created a new key pair for SSH access to EC2 instances

---

## 🖥️ Phase 3: Compute Setup

### 9. EC2 Instances - Web Tier

- Amazon Linux 2023
- Installed Apache (`httpd`)
- Deployed in **public subnet**

### 10. EC2 Instances - App Tier

- Amazon Linux 2023
- Installed Apache + PHP + `php-mysqlnd`
- Deployed in **private subnet**

### 11. Load Balancers

- **Skipped** in this implementation to simplify setup

---

## 💾 Phase 4: Database Tier

### 12. RDS MySQL

- Created **Amazon RDS** (MySQL 8.0)
- Deployed in private subnets across 2 AZs
- No public access, only accessible via App Tier SG

### 13. DB Connection Test

- Wrote a `db-test.php` script on App EC2:

```php
<?php
$host = "<RDS-ENDPOINT>";
$user = "admin";
$pass = "<your-password>";
$db = "dipenncpl";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully to RDS! Server time: " . date("Y-m-d H:i:s");
?>
```

- Tested using: `curl http://localhost/db-test.php`
- Result: Successfully connected and printed server time

---

## 🚦 Phase 5: Validation

### 14. Test Flow

- ✅ Browser → Web EC2 (Apache running)
- ✅ Web EC2 → App EC2 (SSH confirmed)
- ✅ App EC2 → RDS (private IP + endpoint tested)

### 15. Monitoring & Logs

- Verified Apache logs in `/var/log/httpd`
- Checked RDS performance via CloudWatch

---

## ✅ Project Goals Achieved

- Tiered deployment: Web → App → DB
- Subnet isolation and security group segmentation
- No public access to DB
- No ASG used (as per scope)
- Project completed manually via AWS Console

---

## 🧼 Phase 6: Cleanup (Optional)

- Terminate EC2 Instances
- Delete RDS Instance
- Remove NAT Gateway (avoid charges)
- Delete VPC and all associated resources

---

## 📁 Folder Structure (If Uploading Scripts)

```bash
.
├── README.md
├── scripts/
│   └── db-test.php
```

---

## 📌 Notes

- Instance Type Used: t2.micro (Free Tier eligible)
- OS: Amazon Linux 2023
- RDS Engine: MySQL 8.0

---

## 📞 Support / Contact

Created by **Dipen Patel** for AWS Cloud Engineering learning.

---

