# 🚀 Tutorial: Hospedando no InfinityFree (CereniaPet v3.1.0)

Este guia explica como subir seu sistema **CodeIgniter 4** para o InfinityFree. Como o InfinityFree é um serviço gratuito, ele tem algumas limitações e uma estrutura de pastas específica (geralmente `htdocs` ou `public_html`).

---

## 1. Preparação do Banco de Dados

1. Acesse o seu **phpMyAdmin** local (XAMPP).
2. Selecione o banco de dados do projeto.
3. Clique na aba **Exportar** e depois em **Executar**. Salve o arquivo `.sql`.
4. No painel do InfinityFree:
   - Vá em **MySQL Databases**.
   - Crie um novo banco de dados.
   - Anote o **Nome do Banco**, **Usuário**, **Senha** e **Hostname** (geralmente algo como `sql123.infinityfree.com`).
   - Acesse o phpMyAdmin do InfinityFree e **Importe** o arquivo `.sql` que você baixou.

---

## 2. Ajuste na Estrutura de Pastas

O CodeIgniter 4 coloca o arquivo `index.php` dentro da pasta `/public`, mas o InfinityFree espera que tudo o que for público esteja na raiz da pasta `htdocs`.

### Opção Recomendada: Estrutura Segura
Mova o conteúdo da pasta `/public` do seu projeto para a pasta `htdocs` do servidor. O restante do projeto (app, system, writable) deve ficar **fora** da `htdocs` ou em uma subpasta lateral por segurança.

Se você decidir subir tudo para dentro da `htdocs`, crie um arquivo `.htaccess` na raiz da `htdocs` com este conteúdo:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

---

## 3. Configurações do CodeIgniter (.env)

No servidor, você precisará editar o arquivo `.env` (ou configurar via `app/Config/Database.php` e `app/Config/App.php`).

### No arquivo `.env`:
1. **CI_ENVIRONMENT**: Mude para `production`.
2. **app.baseURL**: Coloque a URL que o InfinityFree te deu (ex: `http://seusite.infinityfreeapp.com/`).
3. **Database**:
   - `database.default.hostname`: O hostname do InfinityFree.
   - `database.default.database`: O nome do banco criado.
   - `database.default.username`: O usuário do banco.
   - `database.default.password`: A senha do banco.
   - `database.default.DBDriver`: `MySQLi`

---

## 4. Subindo os Arquivos

1. Recomendo usar um cliente FTP como o **FileZilla**.
2. Pegue os dados de acesso FTP no painel do InfinityFree (**FTP Details**).
3. Envie todos os arquivos do projeto.
   - **IMPORTANTE:** A pasta `writable` deve ter permissão de escrita (CHMOD 777 ou 755 no servidor).

---

## 5. Dicas Extras para InfinityFree

- **Versão do PHP:** Certifique-se de que o InfinityFree está usando PHP 8.1 ou superior (o CI4 exige isso). Você pode mudar isso no painel (Alter PHP Version).
- **Limite de CPU:** O InfinityFree tem limites rígidos. Evite processos muito pesados ou muitos usuários simultâneos no plano gratuito.
- **Erro de HTTPS:** Se o site carregar sem estilos, verifique se a `baseURL` no `.env` está exatamente igual à URL que você está acessando (com ou sem `http/https`).

---

**Sucesso no Deploy! 🚀**
