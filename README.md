# Sistema de Cadastro Imobiliário
### Prefeitura Municipal de São Leopoldo — Secretaria da Fazenda / IPTU

---

## 📦 Estrutura do projeto

```
sistema/
├── login.php                  ← Página de login
├── logout.php                 ← Script de logout
├── database.sql               ← Script do banco de dados (execute primeiro!)
│
├── config/
│   ├── app.php                ← URL base e configurações gerais
│   ├── database.php           ← Conexão com o MySQL (edite aqui)
│   └── auth.php               ← Controle de sessão
│
├── pages/
│   ├── dashboard.php          ← Tela inicial com totais e últimos registros
│   ├── pessoas.php            ← CRUD de Pessoas (contribuintes)
│   └── imoveis.php            ← CRUD de Imóveis
│
├── includes/
│   ├── header.php             ← Cabeçalho HTML + navbar
│   └── footer.php             ← Rodapé HTML
│
└── assets/
    ├── css/style.css          ← Todo o CSS do sistema
    └── js/app.js              ← JavaScript (modais, máscaras, toasts)
```

---

## 🚀 Instalação — passo a passo

### PASSO 1 — Instalar o XAMPP

1. Acesse **https://www.apachefriends.org** e baixe o XAMPP para Windows
2. Instale normalmente (next, next, finish)
3. Abra o **XAMPP Control Panel**
4. Clique em **Start** nos módulos **Apache** e **MySQL**
5. Os dois devem ficar com fundo **verde**

---

### PASSO 2 — Copiar o projeto

1. Abra o Windows Explorer
2. Navegue até: `C:\xampp\htdocs\`
3. **Cole a pasta `sistema`** inteira lá dentro

O resultado deve ser: `C:\xampp\htdocs\sistema\`

---

### PASSO 3 — Criar o banco de dados

1. Com o XAMPP rodando, abra o navegador e acesse:
   **http://localhost/phpmyadmin**

2. No menu lateral esquerdo, clique em **Novo** (ou "New")

3. Na tela que abrir, clique na aba **SQL** (no menu do topo)

4. Abra o arquivo `database.sql` (está dentro da pasta `sistema`)
   — abra com o Bloco de Notas, selecione tudo (Ctrl+A) e copie (Ctrl+C)

5. Cole o conteúdo no campo de SQL do phpMyAdmin

6. Clique em **Executar** (botão azul no canto inferior direito)

7. No menu lateral deve aparecer o banco **cadastro_imoveis** com as tabelas:
   - `usuarios`
   - `pessoas`
   - `imoveis`

---

### PASSO 4 — Acessar o sistema

Abra o navegador e acesse:

```
http://localhost/sistema/login.php
```

**Credenciais de acesso:**
| Campo  | Valor      |
|--------|-----------|
| Usuário | `admin`   |
| Senha   | `password` |

---

### PASSO 5 — Pronto! ✅

O sistema já vem com 3 pessoas e 3 imóveis de exemplo para você testar.

---

## ⚙️ Configuração (se necessário)

### Alterar credenciais do banco

Abra `config/database.php` e edite:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cadastro_imoveis');
define('DB_USER', 'root');   // seu usuário MySQL
define('DB_PASS', '');       // sua senha MySQL (XAMPP padrão: sem senha)
```

### Alterar nome da pasta

Se renomear a pasta `sistema` para outro nome, abra `config/app.php` e altere:

```php
define('BASE_URL', '/sistema');  // troque pelo nome da sua pasta
```

---

## ✅ Funcionalidades

### Obrigatórias (todas implementadas)
- ✅ Dois tipos de cadastro: Pessoa e Imóvel
- ✅ Cadastro de pessoas com todos os campos (id auto-incremento)
- ✅ Campos obrigatórios: nome, nascimento, CPF, sexo
- ✅ Campos opcionais: telefone, e-mail
- ✅ Consulta de pessoas cadastradas
- ✅ Edição e exclusão de pessoas
- ✅ Inscrição Municipal gerada automaticamente
- ✅ Cadastro de imóveis com todos os campos
- ✅ Complemento opcional; demais campos obrigatórios
- ✅ Contribuinte selecionado de pessoas cadastradas
- ✅ Consulta de imóveis com proprietário identificado
- ✅ Edição e exclusão de imóveis

### Extras (também implementadas)
- ✅ Restrição de acesso com usuário e senha (sessão PHP + bcrypt)
- ✅ Filtros de busca por logradouro, bairro e nome do proprietário
- ✅ Busca de pessoas por nome ou CPF
- ✅ Paginação nas listagens
- ✅ Dashboard com totais e registros recentes
- ✅ Máscara de CPF e telefone
- ✅ Validação de CPF duplicado


---

## 🔒 Segurança

- Senhas armazenadas com `password_hash()` — bcrypt
- Queries com **prepared statements PDO** — sem SQL Injection
- Saídas com `htmlspecialchars()` — sem XSS
- Sessão validada em todas as páginas protegidas
- Foreign key impede exclusão de pessoa com imóveis vinculados

---

## 🛠️ Tecnologias

| Camada      | Tecnologia                          |
|-------------|-------------------------------------|
| Back-end    | PHP 8+ (PDO)                        |
| Banco       | MySQL 8 / MariaDB (via XAMPP)       |
| Front-end   | HTML5 + CSS3 customizado            |
| Ícones      | Bootstrap Icons 1.11 (CDN)          |
| Fontes      | Google Fonts — DM Sans + DM Serif   |
