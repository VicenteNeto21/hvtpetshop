<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Sobre o CereniaPet<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Topbar -->
<header class="mb-6 animate-enter">
    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Sobre o Sistema</h1>
    <p class="text-slate-500 text-sm">A história e essência do CereniaPet</p>
</header>

<div class="space-y-6">
    <!-- Cerenia Photo & Initial Story -->
    <div class="grid lg:grid-cols-2 gap-6 items-start animate-enter" style="animation-delay: 0.1s">
        <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 relative group">
            <div id="photo-container" class="relative">
                <div class="absolute -inset-4 bg-brand-100 rounded-3xl transform -rotate-2 transition-transform group-hover:rotate-0"></div>
                <img id="main-cat-img" src="<?= base_url('assets/img/cerenia_3d.png') ?>" alt="Gata Cerenia 3D" class="relative rounded-2xl shadow-xl w-full object-cover aspect-square z-10 border-4 border-white transition-opacity duration-500">
                
                <div class="absolute bottom-4 left-4 z-20 flex gap-2">
                    <button onclick="changeImage('3d')" class="px-3 py-1 bg-white/90 backdrop-blur shadow-sm rounded-full text-[10px] font-bold text-brand-600 hover:bg-brand-600 hover:text-white transition-all">VERSÃO 3D</button>
                    <button onclick="changeImage('real')" class="px-3 py-1 bg-white/90 backdrop-blur shadow-sm rounded-full text-[10px] font-bold text-slate-600 hover:bg-brand-600 hover:text-white transition-all">FOTO REAL</button>
                </div>
            </div>
            <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-brand-500/10 rounded-full blur-2xl"></div>
        </div>
        <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-center h-full">
            <h2 class="text-2xl font-bold text-slate-800 mb-6">Tudo começou com um olhar...</h2>
            <div class="space-y-4 text-slate-600 leading-relaxed">
                <p>
                    O <strong>CereniaPet</strong> não nasceu apenas como um software de gestão. Ele nasceu de uma conexão real, representada pela pequena gata que você vê ao lado: a <strong>Cerenia</strong>.
                </p>
                <p>
                    Cerenia é o coração pulsante deste projeto. Sua presença silenciosa e sua elegância inspiraram a criação de uma ferramenta que busca trazer essa mesma harmonia para o dia a dia dos profissionais amantes de animais.
                </p>
                <p>
                    Assim como cada pet é único, o sistema foi desenhado para tratar cada agendamento, cada banho e cada consulta com a atenção e o carinho que nossos companheiros de quatro patas merecem.
                </p>
            </div>
        </div>
    </div>

    <!-- System Philosophy -->
    <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-slate-100 animate-enter" style="animation-delay: 0.15s">
        <div class="flex items-center gap-4 mb-8 text-brand-600">
            <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center">
                <i data-lucide="heart" class="w-6 h-6"></i>
            </div>
            <h3 class="text-2xl font-bold text-slate-900">Nossa Filosofia</h3>
        </div>
        <div class="grid sm:grid-cols-3 gap-8">
            <div class="space-y-3 border-l-4 border-brand-500 pl-4">
                <h4 class="font-bold text-slate-800">Transparência</h4>
                <p class="text-sm text-slate-500">Dados claros e processos organizados, para que você tenha total controle do seu negócio.</p>
            </div>
            <div class="space-y-3 border-l-4 border-brand-500 pl-4">
                <h4 class="font-bold text-slate-800">Eficiência</h4>
                <p class="text-sm text-slate-500">Agilizar o trabalho braçal para que sobre mais tempo para o que importa: os pets.</p>
            </div>
            <div class="space-y-3 border-l-4 border-brand-500 pl-4">
                <h4 class="font-bold text-slate-800">Inovação</h4>
                <p class="text-sm text-slate-500">Tecnologia de ponta a favor do bem-estar animal, em constante evolução.</p>
            </div>
        </div>
    </div>

    <!-- System Features & Roadmap -->
    <div class="grid lg:grid-cols-2 gap-6 animate-enter" style="animation-delay: 0.2s">
        <!-- Current Features -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
            <div class="flex items-center gap-4 mb-8 text-brand-600">
                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center">
                    <i data-lucide="sparkles" class="w-6 h-6"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Recursos Atuais</h3>
            </div>
            <div class="space-y-6">
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Dashboard Inteligente</h4>
                        <p class="text-sm text-slate-500">Visão panorâmica do seu petshop com KPIs em tempo real, faturamento estimado e ranking de tutores.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="calendar" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Agenda Expressa</h4>
                        <p class="text-sm text-slate-500">Agendamentos rápidos com suporte a recorrência e <strong>cadastro retroativo</strong> para lançar atendimentos passados.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="zap" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Cadastro Rápido Turbinado</h4>
                        <p class="text-sm text-slate-500">Registre tutor e pet em uma única tela com seleção inteligente de raças e telefone opcional.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Fichas Técnicas Digitais</h4>
                        <p class="text-sm text-slate-500">Histórico completo de atendimentos com avaliação de pelagem, comportamento e impressão em A4.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="palette" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Interface Premium</h4>
                        <p class="text-sm text-slate-500">Componentes personalizados, scrollbar estilizada e dropdowns animados para uma experiência visual de alto nível.</p>
                    </div>
                </div>
                <div class="flex items-start gap-4">
                    <div class="p-2 bg-slate-50 rounded-lg text-brand-500">
                        <i data-lucide="smartphone" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800">Tecnologia PWA</h4>
                        <p class="text-sm text-slate-500">Instale o sistema como um aplicativo no seu celular e receba notificações de novos agendamentos.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roadmap -->
        <div class="bg-slate-900 rounded-3xl p-8 shadow-xl text-white">
            <div class="flex items-center gap-4 mb-8 text-brand-400">
                <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center">
                    <i data-lucide="rocket" class="w-6 h-6"></i>
                </div>
                <h3 class="text-2xl font-bold">Roadmap 2026</h3>
            </div>
            <div class="space-y-6">
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-brand-400">Controle de Vacinas</h4>
                        <span class="text-[10px] font-bold bg-brand-500 text-white px-2 py-0.5 rounded-full uppercase tracking-tighter">60%</span>
                    </div>
                    <p class="text-sm text-slate-400">Módulo exclusivo para gestão de ciclos vacinais com alertas automáticos para os tutores via WhatsApp.</p>
                </div>
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-brand-400">Modo Escuro (Dark Mode)</h4>
                        <span class="text-[10px] font-bold bg-brand-500 text-white px-2 py-0.5 rounded-full uppercase tracking-tighter">Iniciando</span>
                    </div>
                    <p class="text-sm text-slate-400">Implementação de tema escuro em todo o sistema para maior conforto visual em atendimentos noturnos.</p>
                </div>
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10 opacity-70">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-bold text-slate-300">Perfil do Petshop</h4>
                        <span class="text-[10px] font-bold bg-slate-700 text-white px-2 py-0.5 rounded-full uppercase tracking-tighter">Em breve</span>
                    </div>
                    <p class="text-sm text-slate-400">Personalização completa do sistema com sua logo, cores e dados empresariais.</p>
                </div>
                <div class="bg-white/5 p-4 rounded-2xl border border-white/10 opacity-70 flex items-center justify-between">
                    <h4 class="font-bold text-slate-400 italic">E muito mais por vir...</h4>
                    <i data-lucide="plus-circle" class="w-4 h-4 text-slate-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Final Call -->
    <div class="text-center pb-12 animate-enter" style="animation-delay: 0.3s">
        <p class="text-slate-500 italic mb-8">
            "Para nós, Cerenia não é apenas um nome, é a promessa de que cada detalhe do sistema foi feito com amor."
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="<?= base_url('dashboard') ?>" class="w-full sm:w-auto px-8 py-4 bg-brand-600 text-white font-bold rounded-2xl shadow-lg shadow-brand-600/20 hover:bg-brand-700 transition-all">
                Voltar ao Dashboard
            </a>
            <p class="text-[10px] text-slate-400">Versão 3.2.0 - Em honra à Cerenia</p>
        </div>
    </div>
</div>

<style>
    @keyframes enter {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-enter {
        animation: enter 0.6s ease-out forwards;
    }

    /* Image Switch Animation */
    .fade-out { opacity: 0; }
    .fade-in { opacity: 1; }
</style>

<script>
    function changeImage(type) {
        const img = document.getElementById('main-cat-img');
        const realUrl = "<?= base_url('assets/img/cerenia.png') ?>";
        const d3Url = "<?= base_url('assets/img/cerenia_3d.png') ?>";
        
        img.classList.add('fade-out');
        
        setTimeout(() => {
            img.src = type === 'real' ? realUrl : d3Url;
            img.alt = type === 'real' ? "Gata Cerenia Real" : "Gata Cerenia 3D";
            img.classList.remove('fade-out');
        }, 300);
    }
</script>
<?= $this->endSection() ?>
