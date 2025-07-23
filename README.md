# AWS Three-Tier Web Architecture - Project Documentation

This repository contains a complete implementation of a **Three-Tier Web Architecture** on AWS, using EC2, RDS, VPC, subnets, NAT/IGW, and tiered security groups. This README provides an in-depth walkthrough of the setup.

---

## 📖 About This Project

This project demonstrates how to build a secure and scalable three-tier architecture on AWS using only the AWS Console. It includes:

- Custom VPC, Subnets, NAT Gateway
- Apache-based EC2 Web/App layers
- MySQL RDS DB (private)
- SSH bastion, internal-only DB access
- Security group isolation per tier

---

## 🧱 Phase 1: Foundation (Networking Basics)

### 1. VPC

- **Name Tag**: `ThreeTier-VPC`
- **CIDR Block**: `10.0.0.0/16`
- **Tenancy**: Default
- **IPv6**: Not enabled
- **DNS Hostnames**: Enabled (for public DNS resolution)

### 2. Subnets

| Tier     | AZ  | Subnet Name | CIDR Block  | Type    |
| -------- | --- | ----------- | ----------- | ------- |
| Web Tier | AZ1 | Web-Pub-1   | 10.0.1.0/24 | Public  |
| Web Tier | AZ2 | Web-Pub-2   | 10.0.2.0/24 | Public  |
| App Tier | AZ1 | App-Priv-1  | 10.0.3.0/24 | Private |
| App Tier | AZ2 | App-Priv-2  | 10.0.4.0/24 | Private |
| DB Tier  | AZ1 | DB-Priv-1   | 10.0.5.0/24 | Private |
| DB Tier  | AZ2 | DB-Priv-2   | 10.0.6.0/24 | Private |

### 3. Internet Gateway (IGW)

- **Name Tag**: `ThreeTier-IGW`
- Created and attached to the custom VPC
- Associated with the route table for public subnets

### 4. Route Tables

- **Public RT (Public-RT)**: Routes `0.0.0.0/0` to IGW; associated with Web-Pub-1 and Web-Pub-2
- **Private RT (Private-RT)**: Routes `0.0.0.0/0` to NAT Gateway; associated with App and DB subnets

### 5. NAT Gateway

- **Name Tag**: `NAT-GW-AZ1`
- Created in public subnet `Web-Pub-1`
- Attached Elastic IP for internet access

---

## 🕸️ Phase 2: Networking & Security

### 6. Security Groups

- **Web-SG**: Allows HTTP (80), SSH (22) from anywhere (0.0.0.0/0)
- **App-SG**: Allows HTTP (80) only from Web-SG
- **DB-SG**: Allows MySQL (3306) only from App-SG

> These security groups were created with tier-based separation and used consistently across EC2 and RDS resources.

### 7. NACLs

- No custom NACLs were created
- Default NACLs were retained, which allow all inbound and outbound traffic
- Snapshots of NACL configuration are documented for reference

### 8. Key Pair

- Created key pair: `three-tier-keypair.pem`
- Used for SSH access to both Web Tier and App Tier EC2 instances

---

## 🖥️ Phase 3: Compute Setup

### 9. EC2 Instances - Web Tier

- **Instance Count**: 1 (Single AZ)
- **Name**: `Bastion-Host`
- **OS**: Amazon Linux 2023
- **Purpose**: Acts as both Web server and Bastion Host for accessing private EC2s
- **Web Server**: Apache (`httpd`) installed
- **Web Page**: Custom portfolio or HTML landing page deployed and served
- **Subnet**: Deployed in public subnet `Web-Pub-1`

### 10. EC2 Instances - App Tier

- **Instance Count**: 1 (Single AZ)
- **OS**: Amazon Linux 2023
- **Software**: Apache + PHP + `php-mysqlnd` installed
- **Script Used**: `db-test.php` (tested RDS connectivity)
- **Access**: Deployed in private subnet `App-Priv-1` with no public IP

### 11. Load Balancers

- Initially created **internet-facing ALB**, but later deleted
- Recreated as an **internal ALB** for App Tier communication
- [Note: ALB details skipped intentionally to simplify this project documentation]

---

## 💾 Phase 4: Database Tier

### 12. RDS MySQL

- **RDS Name**: `dipen-db`
- **Engine**: MySQL 8.0
- **Multi-AZ Deployment**: Yes
- **Subnet Group**: `db-subnet-group-p2` (includes DB-Priv-1 and DB-Priv-2)
- **Access Control**: No public access; traffic restricted to App-SG only

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
- ✅ The App EC2 connects to RDS only through **private network** using private IP or internal endpoint. No internet/public connectivity involved. This ensures a strong layer of security and isolation.

---

## 🚦 Phase 5: Validation

### 14. Test Flow

- ✅ Browser → Web EC2 (Apache running)
- ✅ Web EC2 → App EC2 (SSH confirmed)
- ✅ App EC2 → RDS (private IP + endpoint tested)
- ✅ RDS is only accessible from inside the VPC (no public access)

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

**Architecture:**

```
User (Browser)
   ⬇
Web Tier (HTML/CSS/JS + PHP APIs)
   ⬇
App Tier (PHP logic + DB connector)
   ⬇
DB Tier (Amazon RDS MySQL)
```

---

## 🛠️ Technologies Used

| Layer     | Technology                      |
| --------- | ------------------------------- |
| Web Tier  | EC2 (Amazon Linux 2023, Apache) |
| App Tier  | PHP 8.1, MariaDB client         |
| DB Tier   | Amazon RDS (MySQL 8.0)          |
| Frontend  | HTML5, CSS3, JavaScript         |
| Transport | HTTP via Apache                 |

---

## 🌐 Application Flow

### 1. 🖋️ Submit Employee

- `form.html`: Web form collects Name, Email, Role, Department
- `submit-form.php`: Validates inputs and performs `INSERT INTO employees (...) VALUES (...)`

### 2. 🔎 View Employees

- `view-employees.html`: Fetches employee data using AJAX and populates an HTML table
- `get-employees.php`: Fetches rows from the `employees` table in RDS and returns JSON

---

## 📝 Project File Structure

```bash
/var/www/html/
├── form.html             # Web form to submit employee
├── submit-form.php      # Handles DB insert logic
├── view-employees.html  # Displays data using JS table
└── get-employees.php    # Fetches data from RDS and returns JSON
```

---

## 📊 Database Schema

```sql
CREATE TABLE employees (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  email VARCHAR(255),
  role VARCHAR(100),
  department VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🚀 Deployment Steps (Manual)

### 1. Launch EC2 Instances

- **Web Tier EC2**: Apache + PHP + frontend files
- **App Tier EC2**: PHP CLI + MySQL client (optional testing)

### 2. Create RDS Instance

- Engine: MySQL
- Enable public access if needed
- Store hostname, username, and password

### 3. Configure Security Groups

- Allow HTTP (80) for Web Tier
- Allow inbound MySQL (3306) only from App/Web Tier IPs

### 4. Upload Files

```bash
scp *.php *.html ec2-user@<Web-EC2-Public-IP>:/var/www/html/
```

### 5. Grant File Permissions

```bash
sudo chown apache:apache /var/www/html/*.php
sudo chmod 644 /var/www/html/*.php
```

---

## 🔄 Data Flow Summary

```text
form.html ➔ POST to submit-form.php ➔ INSERT INTO RDS

view-employees.html ➔ AJAX GET to get-employees.php ➔ SELECT FROM RDS ➔ Render Table
```

---

## 📄 Sample Record

```json
{
  "id": 1,
  "name": "Dipen Patel",
  "email": "dipen@example.com",
  "role": "Cloud Engineer",
  "department": "DevOps",
  "created_at": "2025-07-23 04:34:32"
}
```

---

## 🤔 Interview/Documentation Snippet

> We implemented a basic Three-Tier architecture on AWS. The web tier hosts the frontend and PHP scripts, which interact with an RDS MySQL database using secure credentials. Data is inserted using a form and retrieved dynamically via API calls returning JSON. Apache on EC2 serves both the form and data views.

---

## 👁️ Live Test URL (Replace with your Public IP)

- Form: `http://<web-ec2-ip>/form.html`
- View: `http://<web-ec2-ip>/view-employees.html`

---

## 🙏 Credits

- Built by Dipen Patel
- Guided by AWS Cloud Engineering Architecture (Three-Tier Model)

---

## 🛡 Security Notes

- All DB credentials are stored in PHP only (no frontend exposure)
- IAM not used here, but can be added for production
- Limit inbound rules in SG to minimum necessary sources

---
---

## 📌 Notes

- Instance Type Used: t2.micro (Free Tier eligible)
- OS: Amazon Linux 2023
- RDS Engine: MySQL 8.0

---


