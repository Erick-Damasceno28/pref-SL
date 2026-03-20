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
        $logr  = trim($_POST['logradouro']  ?? '');
        $num   = trim($_POST['numero']      ?? '');
        $bairro= trim($_POST['bairro']      ?? '');
        $comp  = trim($_POST['complemento'] ?? '') ?: null;
        $pid   = (int)($_POST['pessoa_id']  ?? 0);

        $erros = [];
        if (!$logr)  $erros[] = 'Logradouro é obrigatório.';
        if (!$num)   $erros[] = 'Número é obrigatório.';
        if (!$bairro)$erros[] = 'Bairro é obrigatório.';
        if (!$pid)   $erros[] = 'Contribuinte (proprietário) é obrigatório.';

        if ($pid) {
            $chk = $pdo->prepare('SELECT id FROM pessoas WHERE id = ?');
            $chk->execute([$pid]);
            if (!$chk->fetch()) $erros[] = 'Contribuinte inválido.';
        }

        if (empty($erros)) {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE imoveis SET logradouro=?,numero=?,bairro=?,complemento=?,pessoa_id=? WHERE id=?');
                $stmt->execute([$logr,$num,$bairro,$comp,$pid,$id]);
                $msg = "Imóvel <strong>$logr, $num</strong> atualizado com sucesso!";
            } else {
                $stmt = $pdo->prepare('INSERT INTO imoveis (logradouro,numero,bairro,complemento,pessoa_id) VALUES (?,?,?,?,?)');
                $stmt->execute([$logr,$num,$bairro,$comp,$pid]);
                $inscricao = str_pad($pdo->lastInsertId(), 6, '0', STR_PAD_LEFT);
                $msg = "Imóvel cadastrado! Inscrição Municipal: <strong>#$inscricao</strong>";
            }
            $msgTipo = 'ok';
        } else {
            $msg     = implode(' ', $erros);
            $msgTipo = 'err';
        }
    }

    if ($acao === 'excluir') {
        $id   = (int)($_POST['id'] ?? 0);
        $pdo->prepare('DELETE FROM imoveis WHERE id = ?')->execute([$id]);
        $msg     = 'Imóvel excluído com sucesso.';
        $msgTipo = 'ok';
    }
}

$qLogr  = trim($_GET['logradouro']   ?? '');
$qBairro= trim($_GET['bairro']       ?? '');
$qProp  = trim($_GET['proprietario'] ?? '');
$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$limit  = 10;
$offset = ($pagina - 1) * $limit;

$conds = [];
$params = [];
if ($qLogr)  { $conds[] = 'i.logradouro LIKE ?'; $params[] = "%$qLogr%"; }
if ($qBairro){ $conds[] = 'i.bairro LIKE ?';     $params[] = "%$qBairro%"; }
if ($qProp)  { $conds[] = 'p.nome LIKE ?';       $params[] = "%$qProp%"; }
$where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM imoveis i JOIN pessoas p ON i.pessoa_id=p.id $where");
$total->execute($params);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

$stmt = $pdo->prepare("
    SELECT i.*, p.nome AS prop_nome, p.cpf AS prop_cpf
    FROM imoveis i
    JOIN pessoas p ON i.pessoa_id = p.id
    $where
    ORDER BY i.logradouro, i.numero
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$imoveis = $stmt->fetchAll();

$pessoas = $pdo->query('SELECT id, nome, cpf FROM pessoas ORDER BY nome ASC')->fetchAll();

$pageTitle = 'Cadastro de Imóveis';
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
      <h1><i class="bi bi-house-door"></i>Cadastro de Imóveis</h1>
      <p class="page-subtitle">Registro e consulta de imóveis do município de São Leopoldo</p>
    </div>
    <?php if (empty($pessoas)): ?>
      <button class="btn btn-accent" disabled title="Cadastre uma pessoa primeiro">
        <i class="bi bi-plus-lg"></i> Novo Imóvel
      </button>
    <?php else: ?>
      <button class="btn btn-accent" onclick="abrirNovoImovel()">
        <i class="bi bi-plus-lg"></i> Novo Imóvel
      </button>
    <?php endif; ?>
  </div>

  <?php if (empty($pessoas)): ?>
  <div class="alert alert-info" style="margin-bottom:1rem">
    <i class="bi bi-info-circle-fill"></i>
    <span>Para cadastrar um imóvel, primeiro cadastre o proprietário em
      <a href="pessoas.php" style="color:var(--pri);font-weight:600">Cadastro de Pessoas</a>.
    </span>
  </div>
  <?php endif; ?>

  <form method="GET" class="filter-bar">
    <div class="filter-group">
      <label>Logradouro</label>
      <div class="search-wrap">
        <i class="bi bi-search search-icon"></i>
        <input type="text" name="logradouro" class="form-control"
               placeholder="Rua, Avenida..." value="<?= htmlspecialchars($qLogr) ?>">
      </div>
    </div>
    <div class="filter-group">
      <label>Bairro</label>
      <input type="text" name="bairro" class="form-control"
             placeholder="Bairro..." value="<?= htmlspecialchars($qBairro) ?>">
    </div>
    <div class="filter-group">
      <label>Proprietário</label>
      <input type="text" name="proprietario" class="form-control"
             placeholder="Nome..." value="<?= htmlspecialchars($qProp) ?>">
    </div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
    <?php if ($qLogr || $qBairro || $qProp): ?>
    <a href="imoveis.php" class="btn btn-outline"><i class="bi bi-x-lg"></i> Limpar</a>
    <?php endif; ?>
  </form>

  <div class="card">
    <div class="card-header">
      <i class="bi bi-building"></i> Imóveis cadastrados
      <span class="badge-cnt"><?= $totalRows ?></span>
    </div>

    <div class="table-wrap">
      <table class="tbl">
        <thead>
          <tr>
            <th>Inscrição Municipal</th><th>Logradouro</th><th>Número</th>
            <th>Bairro</th><th>Complemento</th><th>Proprietário</th><th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($imoveis)): ?>
          <tr><td colspan="7">
            <div class="empty-state">
              <i class="bi bi-house-slash"></i>
              <p><?= ($qLogr||$qBairro||$qProp) ? 'Nenhum resultado para os filtros informados.' : 'Nenhum imóvel cadastrado ainda.' ?></p>
            </div>
          </td></tr>
        <?php else: ?>
          <?php foreach ($imoveis as $im): ?>
          <tr>
            <td><span class="inscricao">#<?= str_pad($im['id'],6,'0',STR_PAD_LEFT) ?></span></td>
            <td><?= htmlspecialchars($im['logradouro']) ?></td>
            <td><?= htmlspecialchars($im['numero']) ?></td>
            <td><?= htmlspecialchars($im['bairro']) ?></td>
            <td><?= $im['complemento'] ? htmlspecialchars($im['complemento']) : '<span class="muted">—</span>' ?></td>
            <td>
              <div class="prop-name"><?= htmlspecialchars($im['prop_nome']) ?></div>
              <div class="prop-cpf"><?= htmlspecialchars($im['prop_cpf']) ?></div>
            </td>
            <td>
              <button class="btn-icon btn-edit" title="Editar"
                      onclick='abrirEdicaoImovel(<?= json_encode($im) ?>)'>
                <i class="bi bi-pencil-fill"></i>
              </button>
              <form method="POST" style="display:inline">
                <input type="hidden" name="acao" value="excluir">
                <input type="hidden" name="id"   value="<?= $im['id'] ?>">
                <button type="submit" class="btn-icon btn-delete" title="Excluir"
                        onclick="return confirm('Excluir <?= addslashes($im['logradouro'].', '.$im['numero']) ?>?\nEsta ação não pode ser desfeita.')">
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
        <a href="?logradouro=<?= urlencode($qLogr) ?>&bairro=<?= urlencode($qBairro) ?>&proprietario=<?= urlencode($qProp) ?>&pagina=<?= $i ?>"
           class="pgn-btn <?= $i === $pagina ? 'pgn-active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<div class="modal-overlay" id="modal-imovel">
  <div class="modal-box">
    <div class="modal-header">
      <h2><i class="bi bi-house-add-fill"></i><span id="modal-titulo">Novo Imóvel</span></h2>
      <button class="btn-close" onclick="closeModal('modal-imovel')"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" novalidate>
      <input type="hidden" name="acao" value="salvar">
      <input type="hidden" name="id"   id="i-id">
      <div class="modal-body">
        <div id="modal-err" class="alert alert-error" style="display:none">
          <i class="bi bi-exclamation-triangle-fill"></i><span id="modal-err-txt"></span>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Logradouro<span class="required">*</span></label>
            <input type="text" name="logradouro" id="i-logr" class="form-control"
                   placeholder="Ex: Rua Independência" maxlength="200">
          </div>
        </div>
        <div class="form-row cols-2">
          <div class="form-group">
            <label>Número<span class="required">*</span></label>
            <input type="text" name="numero" id="i-num" class="form-control"
                   placeholder="Ex: 452" maxlength="20">
          </div>
          <div class="form-group">
            <label>Bairro<span class="required">*</span></label>
            <input type="text" name="bairro" id="i-bairro" class="form-control"
                   placeholder="Ex: Centro" maxlength="100">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Complemento<span class="optional">(opcional)</span></label>
            <input type="text" name="complemento" id="i-comp" class="form-control"
                   placeholder="Ex: Apartamento 404" maxlength="100">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Contribuinte — Proprietário<span class="required">*</span></label>
            <select name="pessoa_id" id="i-pessoa" class="form-select">
              <option value="">Selecione o proprietário...</option>
              <?php foreach ($pessoas as $p): ?>
              <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['nome']) ?> — CPF: <?= htmlspecialchars($p['cpf']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <p class="hint" style="font-size:.7rem;color:var(--muted);margin-top:.25rem">
              <i class="bi bi-info-circle"></i>
              A Inscrição Municipal é gerada automaticamente pelo sistema.
            </p>
          </div>
        </div>

        <p style="font-size:.72rem;color:var(--muted)"><span class="required">*</span> Obrigatórios</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-imovel')">
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
function abrirNovoImovel() {
  document.getElementById('modal-titulo').textContent = 'Novo Imóvel';
  document.getElementById('i-id').value = '';
  ['i-logr','i-num','i-bairro','i-comp'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('i-pessoa').value = '';
  document.getElementById('modal-err').style.display = 'none';
  openModal('modal-imovel');
}

function abrirEdicaoImovel(im) {
  document.getElementById('modal-titulo').textContent = 'Editar Imóvel';
  document.getElementById('i-id').value     = im.id;
  document.getElementById('i-logr').value   = im.logradouro;
  document.getElementById('i-num').value    = im.numero;
  document.getElementById('i-bairro').value = im.bairro;
  document.getElementById('i-comp').value   = im.complemento || '';
  document.getElementById('i-pessoa').value = im.pessoa_id;
  document.getElementById('modal-err').style.display = 'none';
  openModal('modal-imovel');
}

<?php if ($msgTipo === 'err' && isset($_POST['acao']) && $_POST['acao'] === 'salvar'): ?>
document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('modal-titulo').textContent = <?= json_encode((int)($_POST['id']??0) ? 'Editar Imóvel' : 'Novo Imóvel') ?>;
  document.getElementById('i-id').value     = <?= json_encode($_POST['id']          ?? '') ?>;
  document.getElementById('i-logr').value   = <?= json_encode($_POST['logradouro']  ?? '') ?>;
  document.getElementById('i-num').value    = <?= json_encode($_POST['numero']      ?? '') ?>;
  document.getElementById('i-bairro').value = <?= json_encode($_POST['bairro']      ?? '') ?>;
  document.getElementById('i-comp').value   = <?= json_encode($_POST['complemento'] ?? '') ?>;
  document.getElementById('i-pessoa').value = <?= json_encode($_POST['pessoa_id']   ?? '') ?>;
  openModal('modal-imovel');
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
