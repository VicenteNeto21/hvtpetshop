# HVT PetShop Dashboard

Sistema de gestão para petshops desenvolvido em **CodeIgniter 4** com **TailwindCSS**.

## 🚀 Funcionalidades Atuais
- **Autenticação:** Login seguro, logout e proteção de rotas.
- **Dashboard:** Visão geral com indicadores (KPIs).
- **Pets:**
  - Listagem com layout moderno (Row/Card).
  - Pesquisa instantânea ("Smart Search").
  - Paginação personalizada com Tailwind.
  - Página de detalhes do Pet (Perfil + Histórico).

## 📅 Roadmap (Futuras Implementações)
Conforme solicitado, as seguintes melhorias estão planejadas para o sistema Premium:

### 1. Pets & Clínica
- [ ] **Galeria de Fotos:** Carrossel/Grid para salvar fotos do pet (ex: "Antes e Depois").
- [ ] **Cartão de Vacinas:** Controle digital de vacinas tomadas e próximas doses.
- [ ] **Gráfico de Peso:** Acompanhamento visual da evolução do peso do animal.
- [ ] **Etiquetas de Alerta:** Tags visuais no perfil (ex: "ALÉRGICO", "BRAVO").

### 2. Módulos Pendentes (Migração)
- [ ] **Tutores:** Migrar CRUD e vincular com Pets.
- [ ] **Agendamento:** Criar interface visual de calendário (FullCalendar).
- [ ] **Financeiro:** Dashboard de receitas e despesas.

## 🛠️ Instalação
1. Clone o repositório.
2. Copie `.env.example` para `.env` e configure o banco de dados.
3. Execute `composer update`.
4. Inicie o servidor: `php spark serve`.

## 💻 Tech Stack
- PHP 8.2+
- CodeIgniter 4.7
- TailwindCSS (CDN)
- Lucide Icons
- MySQL
