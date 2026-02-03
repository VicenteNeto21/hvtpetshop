<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Gerenciar Usuários<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8 overflow-x-hidden">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-enter">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Gerenciar Usuários</h1>
                <p class="text-slate-500">Aprovar ou rejeitar solicitações de acesso</p>
            </div>
        </header>

        <!-- Contadores -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 animate-enter" style="animation-delay: 0.1s">
            <a href="?status=pendente" class="bg-amber-50 border border-amber-200 rounded-xl p-4 hover:shadow-md transition-shadow <?= $statusSelecionado == 'pendente' ? 'ring-2 ring-amber-400' : '' ?>">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <i data-lucide="clock" class="w-5 h-5 text-amber-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-700"><?= $contadores['pendentes'] ?></p>
                        <p class="text-xs text-amber-600 font-medium">Pendentes</p>
                    </div>
                </div>
            </a>
            
            <a href="?status=aprovado" class="bg-green-50 border border-green-200 rounded-xl p-4 hover:shadow-md transition-shadow <?= $statusSelecionado == 'aprovado' ? 'ring-2 ring-green-400' : '' ?>">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-700"><?= $contadores['aprovados'] ?></p>
                        <p class="text-xs text-green-600 font-medium">Aprovados</p>
                    </div>
                </div>
            </a>
            
            <a href="?status=rejeitado" class="bg-red-50 border border-red-200 rounded-xl p-4 hover:shadow-md transition-shadow <?= $statusSelecionado == 'rejeitado' ? 'ring-2 ring-red-400' : '' ?>">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-700"><?= $contadores['rejeitados'] ?></p>
                        <p class="text-xs text-red-600 font-medium">Rejeitados</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Filtro -->
        <div class="flex gap-2 mb-6 animate-enter" style="animation-delay: 0.15s">
            <a href="?status=todos" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'todos' ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Todos
            </a>
            <a href="?status=pendente" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'pendente' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Pendentes
            </a>
            <a href="?status=aprovado" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'aprovado' ? 'bg-green-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Aprovados
            </a>
            <a href="?status=rejeitado" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'rejeitado' ? 'bg-red-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Rejeitados
            </a>
        </div>

        <!-- Lista de Usuários -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-enter" style="animation-delay: 0.2s">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="text-left p-4 text-xs font-semibold text-slate-500 uppercase">Usuário</th>
                            <th class="text-left p-4 text-xs font-semibold text-slate-500 uppercase">Email</th>
                            <th class="text-left p-4 text-xs font-semibold text-slate-500 uppercase">Tipo</th>
                            <th class="text-center p-4 text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-left p-4 text-xs font-semibold text-slate-500 uppercase">Cadastro</th>
                            <th class="text-center p-4 text-xs font-semibold text-slate-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400 italic">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($usuarios as $user): ?>
                                <tr id="user-<?= $user['id'] ?>" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold">
                                                <?= strtoupper(substr($user['nome'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <p class="font-medium text-slate-800"><?= $user['nome'] ?></p>
                                                <?php if($user['id'] == session()->get('usuario_id')): ?>
                                                    <span class="text-xs text-brand-600 font-medium">Você</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-slate-600"><?= $user['email'] ?></td>
                                    <td class="p-4">
                                        <?php if($user['id'] != session()->get('usuario_id')): ?>
                                            <button onclick="alternarTipo(<?= $user['id'] ?>)" 
                                                    id="tipo-<?= $user['id'] ?>"
                                                    class="px-2 py-1 rounded-lg text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity <?= $user['tipo'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' ?>"
                                                    title="Clique para alternar tipo">
                                                <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-lg text-xs font-medium <?= $user['tipo'] == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' ?>">
                                                <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php
                                            $statusConfig = [
                                                'pendente' => ['bg-amber-100 text-amber-700 border-amber-200', 'Pendente'],
                                                'aprovado' => ['bg-green-100 text-green-700 border-green-200', 'Aprovado'],
                                                'rejeitado' => ['bg-red-100 text-red-700 border-red-200', 'Rejeitado']
                                            ];
                                            $config = $statusConfig[$user['status']] ?? ['bg-slate-100 text-slate-700', $user['status']];
                                        ?>
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border <?= $config[0] ?>">
                                            <?= $config[1] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-slate-500">
                                        <?= date('d/m/Y H:i', strtotime($user['criado_em'])) ?>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-center gap-2">
                                            <?php if($user['status'] == 'pendente'): ?>
                                                <button onclick="aprovarUsuario(<?= $user['id'] ?>)" 
                                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors" 
                                                        title="Aprovar">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                                <button onclick="rejeitarUsuario(<?= $user['id'] ?>)" 
                                                        class="p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" 
                                                        title="Rejeitar">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            <?php elseif($user['status'] == 'rejeitado'): ?>
                                                <button onclick="aprovarUsuario(<?= $user['id'] ?>)" 
                                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors" 
                                                        title="Aprovar">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if($user['id'] != session()->get('usuario_id')): ?>
                                                <button onclick="excluirUsuario(<?= $user['id'] ?>, '<?= addslashes($user['nome']) ?>')" 
                                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 transition-colors" 
                                                        title="Excluir">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    async function aprovarUsuario(id) {
        if (!confirm('Tem certeza que deseja aprovar este usuário?')) return;
        
        try {
            const response = await fetch('<?= base_url('usuarios/aprovar') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao aprovar usuário');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao aprovar usuário');
        }
    }
    
    async function rejeitarUsuario(id) {
        if (!confirm('Tem certeza que deseja rejeitar este usuário?')) return;
        
        try {
            const response = await fetch('<?= base_url('usuarios/rejeitar') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Erro ao rejeitar usuário');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao rejeitar usuário');
        }
    }
    
    async function excluirUsuario(id, nome) {
        if (!confirm(`Tem certeza que deseja EXCLUIR permanentemente o usuário "${nome}"?`)) return;
        
        try {
            const response = await fetch('<?= base_url('usuarios/excluir') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('user-' + id).remove();
            } else {
                alert(data.message || 'Erro ao excluir usuário');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao excluir usuário');
        }
    }
    
    async function alternarTipo(id) {
        try {
            const response = await fetch('<?= base_url('usuarios/alternar-tipo') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            
            if (data.success) {
                const btn = document.getElementById('tipo-' + id);
                btn.textContent = data.novoTipo.charAt(0).toUpperCase() + data.novoTipo.slice(1);
                
                // Atualizar classes de cor
                if (data.novoTipo === 'admin') {
                    btn.className = 'px-2 py-1 rounded-lg text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity bg-purple-100 text-purple-700';
                } else {
                    btn.className = 'px-2 py-1 rounded-lg text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity bg-slate-100 text-slate-600';
                }
            } else {
                alert(data.message || 'Erro ao alterar tipo');
            }
        } catch (error) {
            console.error(error);
            alert('Erro ao alterar tipo');
        }
    }
</script>
<?= $this->endSection() ?>
