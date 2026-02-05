<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - CereniaPet</title>
    <link rel="icon" type="image/png" href="<?= base_url('icons/favicon.png') ?>">
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="<?= base_url('manifest.webmanifest') ?>" crossorigin="use-credentials">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="CereiaPet">
    <link rel="apple-touch-icon" href="<?= base_url('icons/icon-512x512.png') ?>">
    <meta name="description" content="Sistema de gestão para pet shops - Agendamentos, pets, tutores e serviços">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons (Lucide) -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#2563eb', // Azul Premium
                            600: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Premium Styles */
        body { font-family: 'Outfit', sans-serif; }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        .animate-enter {
            animation: enter 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes enter {
            from { opacity: 0; transform: translateY(10px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        
        /* Premium Scrollbar - Global */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9; /* slate-100 */
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #94a3b8 0%, #64748b 100%); /* slate-400 to slate-500 */
            border-radius: 10px;
            border: 2px solid #f1f5f9;
            transition: background 0.3s;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); /* brand gradient */
        }
        ::-webkit-scrollbar-corner {
            background: #f1f5f9;
        }
        
        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        
        /* Scrollbar compacta para dropdowns */
        .custom-scrollbar::-webkit-scrollbar,
        .ts-dropdown::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb,
        .ts-dropdown::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border: none;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body class="bg-[#f1f5f9] text-slate-800 antialiased min-h-screen">

    <?php if (session()->get('isLoggedIn')): ?>
        <!-- Mobile Header com Hamburger -->
        <header class="md:hidden fixed top-0 left-0 right-0 z-30 bg-slate-900 px-4 py-3 flex items-center justify-between shadow-lg">
            <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-brand-500 flex items-center justify-center text-white">
                    <i data-lucide="cat" class="w-4 h-4"></i>
                </div>
                <span class="text-lg font-bold text-white">Cerenia<span class="text-brand-400">Pet</span></span>
            </a>
            <button onclick="toggleMobileSidebar()" class="p-2 rounded-lg text-white hover:bg-slate-800 transition-colors" id="hamburger-btn">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </header>
        
        <!-- Overlay para fechar sidebar mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden" onclick="toggleMobileSidebar()"></div>
        
        <!-- Barra Lateral -->
        <?= view('components/sidebar') ?>
        
        <!-- Conteúdo Principal -->
        <main class="md:ml-64 min-h-screen transition-all duration-300 p-4 md:p-8 pt-20 md:pt-8">
            <div class="max-w-[1600px] mx-auto">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    <?php else: ?>
        <div class="bg-white min-h-screen">
            <?= $this->renderSection('content') ?>
        </div>
    <?php endif; ?>

    <!-- Modal de Novidades da Versão -->
    <?php 
    $versaoAtualAviso = '3.1.0-PRO';
    $mostrarAviso = false;
    
    if (session()->get('usuario_id')) {
        $versaoVista = session()->get('aviso_visto_versao');
        if (!$versaoVista) {
            // Buscar do banco
            $usuarioModel = new \App\Models\UsuarioModel();
            $usuario = $usuarioModel->find(session()->get('usuario_id'));
            $versaoVista = $usuario['versao_aviso_visto'] ?? null;
            session()->set('aviso_visto_versao', $versaoVista);
        }
        $mostrarAviso = ($versaoVista !== $versaoAtualAviso);
    }
    ?>
    
    <?php if ($mostrarAviso): ?>
    <div id="aviso-funcionalidades" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 sm:p-6">
        <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full text-left relative animate-enter max-h-[95vh] flex flex-col border border-slate-100">
            <!-- Header Decorativo -->
            <div class="h-2 bg-gradient-to-r from-brand-400 via-brand-600 to-indigo-600 w-full"></div>
            
            <button onclick="fecharAvisoNovidades()" 
                class="absolute top-3 right-3 md:top-4 md:right-5 text-slate-400 hover:text-red-500 transition-colors bg-slate-100 md:bg-white hover:bg-red-50 rounded-full p-2 z-10" 
                title="Fechar">
                <i data-lucide="x" class="w-5 h-5 md:w-6 md:h-6"></i>
            </button>
            
            <div class="overflow-y-auto px-6 py-8 md:p-10 custom-scrollbar">
                <!-- Título e Intro -->
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-brand-500 flex items-center justify-center text-white shadow-xl shadow-brand-500/20">
                        <i data-lucide="rocket" class="w-7 h-7"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-900">Novidades CereniaPet</h2>
                        <p class="text-slate-500 font-medium">Versão 3.1.0 — O futuro do seu petshop hoje.</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Coluna: Nesta Versão -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="sparkles" class="w-4 h-4 text-brand-500"></i>
                            Evolução do Sistema
                        </h3>
                        <div class="space-y-6 overflow-y-auto max-h-[350px] pr-2 custom-scrollbar">
                            <!-- Bloco: Design & UX -->
                            <div class="space-y-3">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Design & Experiência</p>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-brand-50 rounded-lg text-brand-600 shrink-0">
                                        <i data-lucide="palette" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Interface Minimalista v3.1</p>
                                        <p class="text-xs text-slate-500">Novo visual Dark Sidebar, ícones flat e cores de alto contraste focados em produtividade.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-brand-50 rounded-lg text-brand-600 shrink-0">
                                        <i data-lucide="mouse-pointer-2" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Navegação Expressa</p>
                                        <p class="text-xs text-slate-500">Transições suaves e carregamento instantâneo entre Dashboard e Agenda.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco: Atendimento -->
                            <div class="space-y-3 pt-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Gestão de Atendimento</p>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 shrink-0">
                                        <i data-lucide="clipboard-check" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Ficha Digital Inteligente</p>
                                        <p class="text-xs text-slate-500">Auto-seleção de serviços agendados, avaliação visual técnica e bloqueio de edição pós-finalização.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 shrink-0">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Impressão A4 Profissional</p>
                                        <p class="text-xs text-slate-500">Geração de documentos técnicos otimizados para arquivamento físico.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600 shrink-0">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Agendamentos Recorrentes</p>
                                        <p class="text-xs text-slate-500">Recurso de repetição semanal ou mensal para tutores fidelizados.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Bloco: Administração -->
                            <div class="space-y-3 pt-2">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Controle & Admin</p>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600 shrink-0">
                                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Gestão de Permissões</p>
                                        <p class="text-xs text-slate-500">Controle completo de usuários, cargos e níveis de acesso ao sistema.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600 shrink-0">
                                        <i data-lucide="line-chart" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">Indicadores de Performance</p>
                                        <p class="text-xs text-slate-500">Ranking de clientes e estatísticas de atendimentos unificados (Harmonia).</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coluna: Em Breve -->
                    <div class="bg-slate-50/50 p-6 rounded-2xl border border-slate-100 h-fit">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-brand-500"></i>
                            Roadmap 2026
                        </h3>
                        <ul class="space-y-4">
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                                    <i data-lucide="syringe" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Controle de Vacinas</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Desenvolvimento 60%</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                                    <i data-lucide="package" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Controle de Estoque</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Próxima Fase</p>
                                </div>
                            </li>
                            <li class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-700">Relatórios Financeiros</p>
                                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Planejamento</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <!-- Footer do Modal -->
                <div class="mt-10 flex flex-col md:flex-row items-center justify-between gap-4 border-t border-slate-100 pt-6 text-center md:text-left sticky bottom-0 bg-white pb-2">
                    <p class="text-[10px] md:text-xs text-slate-400 max-w-[200px]">Esta mensagem aparecerá apenas uma vez por atualização.</p>
                    <button onclick="fecharAvisoNovidades()" 
                        class="w-full md:w-auto bg-slate-900 hover:bg-black text-white font-bold py-3 px-10 rounded-2xl shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98]">
                        Vamos lá!
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        const baseUrl = '<?= base_url() ?>';
        
        // Initialize Icons
        lucide.createIcons();
        
        // Função para fechar modal de novidades
        function fecharAvisoNovidades() {
            fetch('<?= base_url('utils/marcar-aviso-visto') ?>', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('aviso-funcionalidades').remove();
                    }
                })
                .catch(() => {
                    // Remove mesmo em caso de erro
                    document.getElementById('aviso-funcionalidades')?.remove();
                });
        }

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js')
                    .then((registration) => {
                        console.log('✅ Service Worker registrado:', registration.scope);
                    })
                    .catch((error) => {
                        console.log('❌ Service Worker falhou:', error);
                    });
            });
        }
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
