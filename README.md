# CereniaPet Dashboard (HVT PetShop)

Sistema premium de gestão para clínicas veterinárias e petshops, desenvolvido em **CodeIgniter 4** com **TailwindCSS**, projetado para máxima eficiência e beleza visual.

## 🚀 Funcionalidades Atuais (Versão 3.4.0)

- **Interface Premium ("Banho de Loja"):** UI polida, responsiva, com componentes modernos, tabelas interativas e total adequação à harmonização visual.
- **Módulo de Saúde e Vacinas:** 
  - Controle digital de imunização e medicamentos.
  - Recálculo inteligente automático para próximas doses e séries vacinais.
  - Exclusão em cadeia (apaga doses futuras automaticamente).
- **Dashboard e KPIs:** Visão geral estratégica com indicadores em tempo real.
- **Agenda Inteligente:** Calendário rápido, cadastro retroativo de atendimentos e função "Salvar e Agendar Outro" em lote.
- **Fichas Técnicas Digitais:** Histórico completo (serviços, comportamento, pelagem) e geração de documento A4 profissional para impressão.
- **Gestão Segura (Soft Deletes):** Exclusão de registros movidos para lixeira segura sem corromper o histórico (Tutores, Pets, Agendamentos, Vacinas, etc).
- **Autenticação:** Login seguro, proteção global de rotas e perfis de acesso.

---

## 📅 Roadmap 2026 (Próximas Atualizações)

As seguintes melhorias estão planejadas para as próximas versões do sistema:

### 1. Automação e Comunicação
- [ ] **Lembretes por WhatsApp:** Envio automático de mensagens para o tutor sobre agendamentos próximos e vacinas vencendo.
- [ ] **Campanhas de Marketing:** Envio de promoções de banho e tosa para clientes inativos.

### 2. Módulos Adicionais
- [ ] **Controle de Estoque:** Gestão de entrada e saída de produtos, rações e medicamentos.
- [ ] **Módulo Financeiro:** Fluxo de caixa detalhado, DRE gerencial e comissões de banhistas/veterinários.
- [ ] **Perfil da Clínica:** Upload da logo do seu petshop para personalizar a interface e as impressões.

### 3. Melhorias na Ficha do Pet
- [ ] **Galeria de Fotos:** Carrossel/Grid para salvar o "Antes e Depois" da tosa e fotos clínicas.
- [ ] **Gráfico de Peso:** Acompanhamento visual da evolução de peso corporal do animal.
- [ ] **Modo Escuro Global (Dark Mode):** Tema visual noturno para a interface inteira do sistema, poupando a vista em atendimentos longos.

---

## 🛠️ Instalação (Local)

1. Clone o repositório (`git clone https://github.com/VicenteNeto21/hvtpetshop.git`).
2. Copie o `.env.example` para `.env` e configure suas credenciais de banco de dados (`database.default.database = hvt_petshop`).
3. Instale as dependências com `composer update`.
4. Importe o banco de dados atualizado ou rode o SQL fornecido na raiz.
5. Inicie o servidor local: `php spark serve`.

## 💻 Stack Tecnológico
- **Backend:** PHP 8.2+ e CodeIgniter 4.7
- **Frontend:** Vanilla JS e TailwindCSS v3 (via CDN)
- **Ícones:** Lucide Icons
- **Banco de Dados:** MariaDB / MySQL
