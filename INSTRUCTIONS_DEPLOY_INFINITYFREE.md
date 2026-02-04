# Instruções para Deploy na InfinityFree (CereniaPet)

Como o sistema foi adaptado para rodar diretamente da pasta `htdocs`, siga estes passos simples para colocar o site no ar:

## 1. Copiar os Arquivos
Mova todos os arquivos da pasta local do seu computador para a pasta **`htdocs`** no servidor da InfinityFree.

> [!IMPORTANT]
> **NÃO COPIE** apenas o conteúdo da pasta `public`. 
> Você deve copiar **TODAS** as pastas da raiz do projeto (`app`, `system`, `writable`, `assets`, `icons`, `vendor`, etc) para dentro da `htdocs`.

## 2. Ponto de Entrada
Note que agora existe um arquivo `index.php` e um `.htaccess` diretamente na raiz do projeto. Isso permite que o sistema funcione sem precisar digitar `/public` na URL.

## 3. Configuração Final do .env
Eu já configurei o banco de dados conforme você passou:
- **Host:** sql308.infinityfree.com
- **Banco:** if0_39359166_hvt_petshop_2
- **Usuário:** if0_39359166
- **Senha:** K3JCBE3vB4XX

### Verifique a URL Base:
Abra o arquivo `.env` no servidor e procure pela linha `app.baseURL`. Ajuste-a para a URL do seu site:
```properties
app.baseURL = 'http://seu-dominio-na-infinityfree.com/'
```

## 4. Pastas de Escrita
Certifique-se de que a pasta `writable` e suas subpastas tenham permissão de escrita no servidor (geralmente a InfinityFree já cuida disso, mas se der erro de log, verifique as permissões 755 ou 777).

## 5. Limpeza de Cache
Após o deploy, ao acessar o site, pressione `Ctrl + Shift + R` para limpar o cache do navegador e garantir que o novo PWA e ícones carreguem corretamente.

---
🚀 **Seu sistema está pronto para decolar!**
