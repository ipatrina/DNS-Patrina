# DNS Patrina

DNS Patrina is a user-side Dynamic Domain Name System (DDNS) update HTTP API server program. Supports updating a single IPv4 or IPv6 address to an individual subdomain.

DNS Patrina 是一款用户侧动态域名更新(DDNS)的HTTP API服务端程序。支持单IPv4或IPv6地址更新至独立子域名。

# Operate environment / 运行环境

- PHP 8 and above.

- PHP 8 及以上版本。

# Supported platforms / 支持平台

DNS Patrina 6 supports the following DNS service backends:

**ZONE** - Update the A or AAAA record to a local file. You may use a custom DNS server program to read the file content and return the resolution result.

**DNSOWL** - Update the A or AAAA record of domain names you purchased from NameSilo. However, NameSilo's own DNS server has a large update delay.

---

DNS Patrina 6 支持以下DNS服务后端：

**ZONE** - 将A或AAAA记录更新至本地文件。您可以使用自定义DNS服务程序读取文件内容并返回解析结果。

**DNSOWL** - 更新您从NameSilo购买的域名的A或AAAA记录。但NameSilo的DNS记录更新延迟较大。

# Configurations / 配置说明

- Edit "config.php" to configure the DNS backend information and user login credentials.

- DNS Patrina's HTTP API complies with the request format of mainstream DDNS providers. Example: http://username:password@ddns.my-api-server.com/ph/update?hostname=subdomain.example.com&myip=192.0.2.1

- To achieve consistency with the HTTP address format of mainstream DDNS providers, you may need to configure some URL rewrite rules. The sample Nginx rules are as follows:

---

- 编辑"config.php"配置DNS服务端信息，以及用户侧登录凭据。

- DNS Patrina的HTTP API符合主流DDNS提供商的请求格式。示例：http://username:password@ddns.my-api-server.com/ph/update?hostname=subdomain.example.com&myip=192.0.2.1

- 为了实现与主流DDNS提供商的HTTP地址格式兼容，您可能需要配置一些URL重写规则。示例Nginx规则如下：

```
rewrite /dyndns/update /path/to/dns_patrina/index.php;
rewrite /nic/update /path/to/dns_patrina/index.php;
rewrite /ph/update /path/to/dns_patrina/index.php;
```
