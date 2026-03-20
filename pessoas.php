<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$pdo = getDB();
$msg = '';
$msgTipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'salvar') {
        $id    = (int)($_POST['id'] ?? 0);
        $nome  = trim($_POST['nome'] ?? '');
        $nasc  = $_POST['data_nascimento'] ?? '';
        $cpf   = trim($_POST['cpf'] ?? '');
        $sexo  = $_POST['sexo'] ?? '';
        $tel   = trim($_POST['telefone'] ?? '') ?: null;
        $email = trim($_POST['email'] ?? '') ?: null;

        $erros = [];
        if (!$nome)                      $erros[] = 'Nome é obrigatório.';
        if (!$nasc)                      $erros[] = 'Data de nascimento é obrigatória.';
        if (!$cpf)                       $erros[] = 'CPF é obrigatório.';
        if (!in_array($sexo,['M','F','O'])) $erros[] = 'Sexo inválido.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';

        if ($cpf) {
            $chk = $pdo->prepare('SELECT id FROM pessoas WHERE cpf = ? AND id != ?');
            $chk->execute([$cpf, $id]);
            if ($chk->fetch()) $erros[] = 'CPF já cadastrado para outra pessoa.';
        }

        if (empty($erros)) {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE pessoas SET nome=?,data_nascimento=?,cpf=?,sexo=?,telefone=?,email=? WHERE id=?');
                $stmt->execute([$nome,$nasc,$cpf,$sexo,$tel,$email,$id]);
                $msg = "Pessoa <strong>$nome</strong> atualizada com sucesso!";
            } else {
                $stmt = $pdo->prepare('INSERT INTO pessoas (nome,data_nascimento,cpf,sexo,telefone,email) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$nome,$nasc,$cpf,$sexo,$tel,$email]);
                $msg = "Pessoa <strong>$nome</strong> cadastrada com sucesso!";
            }
            $msgTipo = 'ok';
        } else {
            $msg     = implode(' ', $erros);
            $msgTipo = 'err';
        }
    }

    if ($acao === 'excluir') {
        $id   = (int)($_POST['id'] ?? 0);
        $vinc = $pdo->prepare('SELECT COUNT(*) FROM imoveis WHERE pessoa_id = ?');
        $vinc->execute([$id]);
        if ($vinc->fetchColumn() > 0) {
            $msg     = 'Não é possível excluir: esta pessoa possui imóveis vinculados. Remova os imóveis primeiro.';
            $msgTipo = 'err';
        } else {
            $pdo->prepare('DELETE FROM pessoas WHERE id = ?')->execute([$id]);
            $msg     = 'Pessoa excluída com sucesso.';
            $msgTipo = 'ok';
        }
    }
}

$q      = trim($_GET['q'] ?? '');
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$limit  = 10;
$offset = ($pagina - 1) * $limit;
$where  = $q ? 'WHERE nome LIKE ? OR cpf LIKE ?' : '';
$params = $q ? ["%$q%", "%$q%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM pessoas $where");
$total->execute($params);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

$stmt = $pdo->prepare("SELECT * FROM pessoas $where ORDER BY nome ASC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$pessoas = $stmt->fetchAll();

function sexoBadge(string $s): string {
    $m = ['M'=>['Masculino','badge-m'],'F'=>['Feminino','badge-f'],'O'=>['Outro','badge-o']];
    [$l,$c] = $m[$s] ?? ['—',''];
    return "<span class=\"badge $c\">$l</span>";
}

$pageTitle = 'Cadastro de Pessoas';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:1400px;margin:0 auto;padding:1.6rem 1.4rem">

  <?php if ($msg): ?>
  <div class="alert alert-<?= $msgTipo === 'ok' ? 'success' : 'error' ?> auto-dismiss">
    <i class="bi bi-<?= $msgTipo === 'ok' ? 'check-circle' : 'exclamation-triangle' ?>-fill"></i>
    <span><?= $msg ?></span>
  </div>
  <?php endif; ?>

  <div class="page-header">
    <div>
      <h1><i class="bi bi-people"></i>Cadastro de Pessoas</h1>
      <p class="page-subtitle">Gerenciamento de contribuintes e proprietários de imóveis</p>
    </div>
    <button class="btn btn-accent" onclick="abrirNovaPessoa()">
      <i class="bi bi-plus-lg"></i> Nova Pessoa
    </button>
  </div>

  <form method="GET" class="filter-bar">
    <div class="filter-group">
      <label>Buscar pessoa</label>
      <div class="search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="text" name="q" class="form-control"
               placeholder="Nome ou CPF..." value="<?= htmlspecialchars($q) ?>">
      </div>
    </div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
    <?php if ($q): ?>
    <a href="pessoas.php" class="btn btn-outline"><i class="bi bi-x-lg"></i> Limpar</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <div class="card-header">
      <i class="bi bi-table"></i> Pessoas cadastradas
      <span class="badge-cnt"><?= $totalRows ?></span>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID</th><th>Nome</th><th>CPF</th><th>Nascimento</th>
            <th>Sexo</th><th>Telefone</th><th>E-mail</th><th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($pessoas)): ?>
          <tr><td colspan="8">
            <div class="empty-state">
              <i class="bi bi-person-slash"></i>
              <p><?= $q ? "Nenhum resultado para \"".htmlspecialchars($q)."\"." : 'Nenhuma pessoa cadastrada ainda.' ?></p>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($pessoas as $p): ?>
          <tr>
            <td><span class="inscricao">#<?= str_pad($p['id'],4,'0',STR_PAD_LEFT) ?></span></td>
            <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
            <td style="font-family:monospace;font-size:.76rem"><?= htmlspecialchars($p['cpf']) ?></td>
            <td><?= $p['data_nascimento'] ? date('d/m/Y', strtotime($p['data_nascimento'])) : '—' ?></td>
            <td><?= sexoBadge($p['sexo']) ?></td>
            <td><?= htmlspecialchars($p['telefone'] ?? '—') ?></td>
            <td style="font-size:.76rem"><?= htmlspecialchars($p['email'] ?? '—') ?></td>
            <td>
              <button class="btn-icon btn-edit" title="Editar"
                      onclick='abrirEdicaoPessoa(<?= json_encode($p) ?>)'>
                <i class="bi bi-pencil-fill"></i>
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id"   value="<?= $p['id'] ?>">
                <button type="submit" class="btn-icon btn-delete" title="Excluir"
                        onclick="return confirm('Excluir <?= addslashes($p['nome']) ?>?\nEsta ação não pode ser desfeita.')">
                  <i class="bi bi-trash-fill"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="table-footer">
      <small>
        <?php if ($totalRows): ?>
          Exibindo <?= min($offset+1,$totalRows) ?>–<?= min($offset+$limit,$totalRows) ?> de <?= $totalRows ?>
        <?php endif; ?>
      </small>
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?q=<?= urlencode($q) ?>&pagina=<?= $i ?>"
           class="pgn-btn <?= $i === $pagina ? 'pgn-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<div class="modal-overlay" id="modal-pessoa">
  <div class="modal-box">
    <div class="modal-header">
      <h2><i class="bi bi-person-plus-fill"></i><span id="modal-titulo">Nova Pessoa</span></h2>
      <button class="btn-close" onclick="closeModal('modal-pessoa')"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" novalidate>
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id"   id="p-id">
      <div class="modal-body">
        <div id="modal-err" class="alert alert-error" style="display:none">
          <i class="bi bi-exclamation-triangle-fill"></i><span id="modal-err-txt"></span>
        </div>

        <div class="form-row cols-3">
          <div class="form-group">
            <label>Nome<span class="required">*</span></label>
            <input type="text" name="nome" id="p-nome" class="form-control" maxlength="150">
          </div>
          <div class="form-group">
            <label>Sexo<span class="required">*</span></label>
            <select name="sexo" id="p-sexo" class="form-select">
              <option value="">Selecione...</option>
              <option value="M">Masculino</option>
              <option value="F">Feminino</option>
              <option value="O">Outro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Nascimento<span class="required">*</span></label>
            <input type="date" name="data_nascimento" id="p-nasc" class="form-control">
          </div>
        </div>

        <div class="form-row cols-2">
          <div class="form-group">
            <label>CPF<span class="required">*</span></label>
            <input type="text" name="cpf" id="p-cpf" class="form-control" placeholder="000.000.000-00" maxlength="14">
          </div>
          <div class="form-group">
            <label>Telefone<span class="optional">(opcional)</span></label>
            <input type="text" name="telefone" id="p-tel" class="form-control" placeholder="(00) 00000-0000" maxlength="15">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>E-mail<span class="optional">(opcional)</span></label>
            <input type="email" name="email" id="p-email" class="form-control" maxlength="150">
          </div>
        </div>

        <p style="font-size:.72rem;color:var(--muted)"><span class="required">*</span> Obrigatórios</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-pessoa')">
          <i class="bi bi-x-lg"></i> Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirNovaPessoa() {
  document.getElementById('modal-titulo').textContent = 'Nova Pessoa';
  document.getElementById('p-id').value = '';
  ['p-nome','p-nasc','p-cpf','p-tel','p-email'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('p-sexo').value = '';
  document.getElementById('modal-err').style.display = 'none';
  openModal('modal-pessoa');
}

function abrirEdicaoPessoa(p) {
  document.getElementById('modal-titulo').textContent  = 'Editar Pessoa';
  document.getElementById('p-id').value    = p.id;
  document.getElementById('p-nome').value  = p.nome;
  document.getElementById('p-nasc').value  = p.data_nascimento;
  document.getElementById('p-cpf').value   = p.cpf;
  document.getElementById('p-sexo').value  = p.sexo;
  document.getElementById('p-tel').value   = p.telefone || '';
  document.getElementById('p-email').value = p.email    || '';
  document.getElementById('modal-err').style.display = 'none';
  openModal('modal-pessoa');
}

<?php if ($msgTipo === 'err' && isset($_POST['acao']) && $_POST['acao'] === 'salvar'): ?>
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('modal-titulo').textContent = <?= json_encode((int)($_POST['id']??0) ? 'Editar Pessoa' : 'Nova Pessoa') ?>;
  document.getElementById('p-id').value    = <?= json_encode($_POST['id']   ?? '') ?>;
  document.getElementById('p-nome').value  = <?= json_encode($_POST['nome'] ?? '') ?>;
  document.getElementById('p-nasc').value  = <?= json_encode($_POST['data_nascimento'] ?? '') ?>;
  document.getElementById('p-cpf').value   = <?= json_encode($_POST['cpf']  ?? '') ?>;
  document.getElementById('p-sexo').value  = <?= json_encode($_POST['sexo'] ?? '') ?>;
  document.getElementById('p-tel').value   = <?= json_encode($_POST['telefone'] ?? '') ?>;
  document.getElementById('p-email').value = <?= json_encode($_POST['email']    ?? '') ?>;
  openModal('modal-pessoa');
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
