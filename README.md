<div align="center">
  <img src="./icons/pet.jpg" alt="Logo HVTPETSHOP" width="120" style="border-radius: 50%;">
  <h1>CereniaPet</h1>
  <p><strong>Sistema de gestão para petshop e clínica veterinária.</strong></p>
  <p>
    <img src="https://img.shields.io/badge/versão-AMPN%201.1.4-blue" alt="Versão do Sistema">
    <img src="https://img.shields.io/badge/PHP-8.x-blueviolet" alt="PHP">
    <img src="https://img.shields.io/badge/Frontend-TailwindCSS-38B2AC" alt="TailwindCSS">
    <img src="https://img-shields.io/badge/Licença-MIT-green" alt="Licença MIT">
  </p>
</div>

# CereniaPet

O CereniaPet é um sistema web desenvolvido para simplificar a gestão de petshops e clínicas veterinárias, oferecendo uma interface intuitiva e responsiva para o controle de pets, tutores e agendamentos.

## 📋 Tabela de Conteúdos

- [CereniaPet](#cereniapet)
  - [📋 Tabela de Conteúdos](#-tabela-de-conteúdos)
  - [✨ Recursos](#-recursos)
  - [🚀 Tecnologias Utilizadas](#-tecnologias-utilizadas)
  - [📂 Estrutura do Projeto](#-estrutura-do-projeto)
  - [🏁 Começando](#-começando)
    - [✅ Pré-requisitos](#-pré-requisitos)
    - [🔧 Instalação](#-instalação)
  - [🎨 Personalização](#-personalização)
  - [🤝 Contribuição](#-contribuição)
  - [📄 Licença](#-licença)
  - [🏆 Créditos](#-créditos)

## ✨ Recursos

- **Gestão de Clientes:** Cadastro, edição e busca de pets e seus respectivos tutores.
- **Controle de Agendamentos:** Sistema para agendar, acompanhar e gerenciar o status dos atendimentos (banho, tosa, consultas), com ficha de atendimento detalhada.
- **Dashboard Intuitivo:** Painel principal com indicadores chave, estatísticas e uma "Agenda do Dia" interativa com ações rápidas.
- **Busca Rápida:** Ferramenta de busca dinâmica (AJAX) para encontrar pets ou tutores rapidamente.
- **Modais de Confirmação:** Confirmações visuais para ações críticas como exclusão de pets/tutores e cancelamento de agendamentos.
- **Geração de PDF:** Fichas de atendimento podem ser geradas em PDF com um layout profissional.
- **Design Responsivo:** Interface adaptável para uma ótima experiência em desktops, tablets e celulares.
- **Notificações:** Aviso modal sobre novas funcionalidades e atualizações do sistema.

## 🚀 Tecnologias Utilizadas

- **Backend:** PHP 8+
- **Banco de Dados:** MySQL (com PDO para conexão)
- **Frontend:** HTML5, TailwindCSS, JavaScript
- **Ícones:** Font Awesome

## 📂 Estrutura do Projeto

```
hvt_petshop/
├── auth/               # Scripts de autenticação (login, logout)
├── config/             # Arquivo de configuração do banco de dados
├── dashboard/          # Páginas relacionadas ao dashboard (ex: indicadores)
├── icons/              # Ícones e imagens do sistema
├── pets/               # CRUD de pets e agendamentos
│   └── agendamentos/
├── tutores/            # CRUD de tutores
├── vendas/             # Módulo de Ponto de Venda (PDV)
├── dashboard.php       # Página principal do sistema
├── login.html          # Página de login
└── readme.md           # Este arquivo
```

## 🏁 Começando

Siga estas instruções para configurar e rodar o projeto em seu ambiente local.

### ✅ Pré-requisitos

- Um ambiente de servidor local como XAMPP ou WAMP, que inclua:
    - Apache
    - PHP 8 ou superior
    - MySQL / MariaDB
    - [Composer](https://getcomposer.org/) instalado globalmente.

### 🔧 Instalação

1. **Clone o repositório ou copie os arquivos** para o diretório do seu servidor web (ex: `C:/xampp/htdocs/hvt_petshop`).

2. **Instale as dependências do PHP:**
   - Abra o terminal na pasta raiz do projeto (`hvt_petshop/`).
   - Rode o comando: `composer install`. Isso instalará as bibliotecas necessárias (como DomPDF e phpdotenv) e criará a pasta `vendor/`.

3. **Configure as variáveis de ambiente:**
   - Renomeie o arquivo `.env.example` para `.env`.
   - Abra o arquivo `.env` e preencha com as suas credenciais do banco de dados:
   ```dotenv
   DB_HOST=localhost
   DB_DATABASE=hvt_petshop_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Crie o banco de dados:**
   - Acesse o `phpMyAdmin` (ou outro gerenciador de banco de dados).
   - Crie um novo banco de dados com o mesmo nome que você definiu em `DB_DATABASE` no arquivo `.env`.
   - Importe o arquivo `.sql` do projeto para criar as tabelas necessárias (se houver um).

5. **Acesse o sistema** pelo seu navegador. Normalmente, o endereço será `http://localhost/hvt_petshop/login.html`.

## 🎨 Personalização

- **Versão do Sistema:** Para alterar o número da versão, edite o texto no arquivo `dashboard.php` (no aviso e no rodapé).
- **Estilos e Cores:** O projeto utiliza **TailwindCSS**. As classes de utilitários estão diretamente no HTML. Para mudanças globais (cores primárias, fontes), você pode criar um arquivo de configuração do Tailwind ou editar as classes existentes nos arquivos `.php`.

## 🤝 Contribuição

Contribuições são bem-vindas! Se você tem sugestões para melhorar o projeto, sinta-se à vontade para criar uma *issue* ou enviar um *pull request*.

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 🏆 Créditos

Desenvolvido com ❤️ por **AMPN**.
