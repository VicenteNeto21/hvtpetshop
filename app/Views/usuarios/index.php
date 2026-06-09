<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Gerenciar Usuários<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter">
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
            
            <a href="?status=aprovado" class="bg-brand-50 border border-brand-200 rounded-xl p-4 hover:shadow-md transition-shadow <?= $statusSelecionado == 'aprovado' ? 'ring-2 ring-brand-400' : '' ?>">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center">
                        <i data-lucide="check-circle" class="w-5 h-5 text-brand-600"></i>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-brand-700"><?= $contadores['aprovados'] ?></p>
                        <p class="text-xs text-brand-600 font-medium">Aprovados</p>
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
        <div class="flex flex-wrap gap-2 mb-6 animate-enter" style="animation-delay: 0.15s">
            <a href="?status=todos" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'todos' ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Todos
            </a>
            <a href="?status=pendente" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'pendente' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Pendentes
            </a>
            <a href="?status=aprovado" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'aprovado' ? 'bg-brand-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Aprovados
            </a>
            <a href="?status=rejeitado" class="px-4 py-2 rounded-lg text-sm font-medium transition-colors <?= $statusSelecionado == 'rejeitado' ? 'bg-red-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' ?>">
                Rejeitados
            </a>
        </div>

        <!-- Lista de Usuários -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-enter" style="animation-delay: 0.2s">
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="hidden sm:block">
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
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold border border-slate-200">
                                                <i data-lucide="user" class="w-5 h-5"></i>
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
                                            <button onclick="openConfirmModal('<?= base_url('usuarios/alternar-tipo/'.$user['id']) ?>', 'Alterar Permissão', 'Tem certeza que deseja alterar o nível de acesso deste usuário para <?= $user['tipo'] == 'admin' ? 'Funcionário' : 'Administrador' ?>?', 'warning', 'shield')"
                                                    class="px-2 py-1 rounded-lg text-xs font-medium cursor-pointer hover:opacity-80 transition-opacity <?= $user['tipo'] == 'admin' ? 'bg-slate-800 text-slate-100' : 'bg-slate-100 text-slate-600' ?>"
                                                    title="Clique para alternar tipo">
                                                <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                            </button>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-lg text-xs font-medium <?= $user['tipo'] == 'admin' ? 'bg-slate-800 text-slate-100' : 'bg-slate-100 text-slate-600' ?>">
                                                <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <?php
                                            $statusConfig = [
                                                'pendente' => ['bg-amber-100 text-amber-700 border-amber-200', 'Pendente'],
                                                'aprovado' => ['bg-brand-50 text-brand-700 border-brand-200', 'Aprovado'],
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
                                                <button onclick="openConfirmModal('<?= base_url('usuarios/aprovar/'.$user['id']) ?>', 'Aprovar Usuário', 'Deseja conceder acesso ao sistema para este usuário?', 'primary', 'check')" 
                                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors tooltip-action" 
                                                        title="Aprovar">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                                <button onclick="openConfirmModal('<?= base_url('usuarios/rejeitar/'.$user['id']) ?>', 'Rejeitar Usuário', 'Deseja negar o acesso deste usuário ao sistema?', 'danger', 'x')" 
                                                        class="p-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors tooltip-action" 
                                                        title="Rejeitar">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            <?php elseif($user['status'] == 'rejeitado'): ?>
                                                <button onclick="openConfirmModal('<?= base_url('usuarios/aprovar/'.$user['id']) ?>', 'Aprovar Usuário', 'Deseja conceder acesso ao sistema para este usuário?', 'primary', 'check')" 
                                                        class="p-2 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors tooltip-action" 
                                                        title="Aprovar">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if($user['id'] != session()->get('usuario_id')): ?>
                                                <button onclick="openConfirmModal('<?= base_url('usuarios/excluir/'.$user['id']) ?>', 'Excluir Usuário', 'Tem certeza que deseja excluir permanentemente o usuário <?= addslashes($user['nome']) ?>?', 'danger', 'trash-2')" 
                                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-red-100 hover:text-red-600 transition-colors tooltip-action" 
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

            <!-- Mobile Cards (hidden on desktop) -->
            <div class="sm:hidden p-4 space-y-3">
                <?php if(empty($usuarios)): ?>
                    <div class="p-8 text-center text-slate-400 italic">
                        Nenhum usuário encontrado.
                    </div>
                <?php else: ?>
                    <?php foreach($usuarios as $user): ?>
                        <?php
                            $statusConfig = [
                                'pendente' => ['bg-amber-100 text-amber-700 border-amber-200', 'Pendente'],
                                'aprovado' => ['bg-brand-50 text-brand-700 border-brand-200', 'Aprovado'],
                                'rejeitado' => ['bg-red-100 text-red-700 border-red-200', 'Rejeitado']
                            ];
                            $config = $statusConfig[$user['status']] ?? ['bg-slate-100 text-slate-700', $user['status']];
                        ?>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <!-- Header: Avatar + Name + Status -->
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-lg shrink-0">
                                    <i data-lucide="user" class="w-6 h-6"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-800 truncate">
                                        <?= $user['nome'] ?>
                                        <?php if($user['id'] == session()->get('usuario_id')): ?>
                                            <span class="text-xs text-brand-600">(Você)</span>
                                        <?php endif; ?>
                                    </h3>
                                    <p class="text-sm text-slate-500 truncate"><?= $user['email'] ?></p>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold border <?= $config[0] ?> shrink-0">
                                    <?= $config[1] ?>
                                </span>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex items-center gap-4 text-xs text-slate-500 mb-3 pb-3 border-b border-slate-200">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                    <?= date('d/m/Y', strtotime($user['criado_em'])) ?>
                                </span>
                                <?php if($user['id'] != session()->get('usuario_id')): ?>
                                    <button onclick="openConfirmModal('<?= base_url('usuarios/alternar-tipo/'.$user['id']) ?>', 'Alterar Permissão', 'Tem certeza que deseja alterar o nível de acesso deste usuário para <?= $user['tipo'] == 'admin' ? 'Funcionário' : 'Administrador' ?>?', 'warning', 'shield')" 
                                            class="px-2 py-0.5 rounded text-xs font-medium <?= $user['tipo'] == 'admin' ? 'bg-slate-800 text-slate-100' : 'bg-slate-200 text-slate-600' ?>">
                                        <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                    </button>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-xs font-medium <?= $user['tipo'] == 'admin' ? 'bg-slate-800 text-slate-100' : 'bg-slate-200 text-slate-600' ?>">
                                        <?= ucfirst($user['tipo'] ?? 'funcionario') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <?php if($user['status'] == 'pendente'): ?>
                                    <button onclick="openConfirmModal('<?= base_url('usuarios/aprovar/'.$user['id']) ?>', 'Aprovar Usuário', 'Deseja conceder acesso ao sistema para este usuário?', 'primary', 'check')" 
                                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-green-100 text-green-600 text-xs font-medium hover:bg-green-200 transition-colors">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Aprovar
                                    </button>
                                    <button onclick="openConfirmModal('<?= base_url('usuarios/rejeitar/'.$user['id']) ?>', 'Rejeitar Usuário', 'Deseja negar o acesso deste usuário ao sistema?', 'danger', 'x')" 
                                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-red-100 text-red-600 text-xs font-medium hover:bg-red-200 transition-colors">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        Rejeitar
                                    </button>
                                <?php elseif($user['status'] == 'rejeitado'): ?>
                                    <button onclick="openConfirmModal('<?= base_url('usuarios/aprovar/'.$user['id']) ?>', 'Aprovar Usuário', 'Deseja conceder acesso ao sistema para este usuário?', 'primary', 'check')" 
                                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-green-100 text-green-600 text-xs font-medium hover:bg-green-200 transition-colors">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        Aprovar
                                    </button>
                                <?php else: ?>
                                    <span class="flex-1 text-center text-xs text-slate-400 py-2">Usuário aprovado</span>
                                <?php endif; ?>
                                
                                <?php if($user['id'] != session()->get('usuario_id')): ?>
                                    <button onclick="openConfirmModal('<?= base_url('usuarios/excluir/'.$user['id']) ?>', 'Excluir Usuário', 'Tem certeza que deseja excluir permanentemente o usuário <?= addslashes($user['nome']) ?>?', 'danger', 'trash-2')" 
                                            class="flex items-center justify-center gap-1 px-4 py-2 rounded-lg bg-slate-200 text-slate-600 text-xs font-medium hover:bg-red-100 hover:text-red-600 transition-colors">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
</div>

<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
