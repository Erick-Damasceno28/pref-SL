<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$pdo = getDB();

$totalPessoas = (int) $pdo->query('SELECT COUNT(*) FROM pessoas')->fetchColumn();
$totalImoveis = (int) $pdo->query('SELECT COUNT(*) FROM imoveis')->fetchColumn();

$recentesImoveis = $pdo->query('
    SELECT i.id, i.logradouro, i.numero, i.bairro, p.nome AS proprietario
    FROM imoveis i
    JOIN pessoas p ON i.pessoa_id = p.id
    ORDER BY i.criado_em DESC
    LIMIT 5
')->fetchAll();

$recentesPessoas = $pdo->query('
    SELECT id, nome, cpf, sexo
    FROM pessoas
    ORDER BY criado_em DESC
    LIMIT 5
')->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:1400px;margin:0 auto;padding:1.6rem 1.4rem">

  <div class="page-header">
    <div>
      <h1><i class="bi bi-speedometer2"></i>Dashboard</h1>
      <p class="page-subtitle">Visão geral do sistema — <?= date('d/m/Y') ?></p>
    </div>
  </div>

  <div class="stats-grid">
    <a href="<?= BASE_URL ?>/pages/pessoas.php" class="stat-card">
      <div class="stat-icon ico-blue"><i class="bi bi-people-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalPessoas ?></div>
        <div class="stat-label">Pessoas cadastradas</div>
      </div>
    </a>
    <a href="<?= BASE_URL ?>/pages/imoveis.php" class="stat-card">
      <div class="stat-icon ico-gold"><i class="bi bi-house-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalImoveis ?></div>
        <div class="stat-label">Imóveis registrados</div>
      </div>
    </a>
    <div class="stat-card" style="cursor:default">
      <div class="stat-icon ico-green"><i class="bi bi-calendar-check-fill"></i></div>
      <div>
        <div class="stat-value" style="font-size:1.15rem;line-height:1.3"><?= date('d/m/Y') ?></div>
        <div class="stat-label">Data atual</div>
      </div>
    </div>
  </div>

  <div class="dash-grid">

    <div class="card">
      <div class="card-header"><i class="bi bi-house-door"></i> Imóveis recentemente cadastrados</div>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>Inscrição</th><th>Endereço</th><th>Proprietário</th></tr></thead>
          <tbody>
          <?php if (empty($recentesImoveis)): ?>
            <tr><td colspan="3">
              <div class="empty-state"><i class="bi bi-house-slash"></i><p>Nenhum imóvel cadastrado ainda.</p></div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($recentesImoveis as $im): ?>
            <tr>
              <td><span class="inscricao">#<?= str_pad($im['id'], 6, '0', STR_PAD_LEFT) ?></span></td>
              <td><?= htmlspecialchars($im['logradouro']) ?>, <?= htmlspecialchars($im['numero']) ?></td>
              <td><?= htmlspecialchars($im['proprietario']) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <a href="<?= BASE_URL ?>/pages/imoveis.php" class="btn btn-primary btn-sm">
          Ver todos <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><i class="bi bi-person-lines-fill"></i> Pessoas recentemente cadastradas</div>
      <div class="table-wrap">
        <table class="tbl">
          <thead><tr><th>Nome</th><th>CPF</th><th>Sexo</th></tr></thead>
          <tbody>
          <?php if (empty($recentesPessoas)): ?>
            <tr><td colspan="3">
              <div class="empty-state"><i class="bi bi-person-slash"></i><p>Nenhuma pessoa cadastrada ainda.</p></div>
            </td></tr>
          <?php else: ?>
            <?php foreach ($recentesPessoas as $p):
              $sexos = ['M'=>['Masculino','badge-m'],'F'=>['Feminino','badge-f'],'O'=>['Outro','badge-o']];
              [$sl,$sc] = $sexos[$p['sexo']] ?? ['—',''];
            ?>
            <tr>
              <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
              <td style="font-family:monospace;font-size:.76rem"><?= htmlspecialchars($p['cpf']) ?></td>
              <td><span class="badge <?= $sc ?>"><?= $sl ?></span></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        <a href="<?= BASE_URL ?>/pages/pessoas.php" class="btn btn-primary btn-sm">
          Ver todas <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
