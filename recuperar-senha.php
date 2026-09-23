<<?php
$mensagem = "";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>FN Agropecuária • Recuperar Senha</title>
  <link rel="stylesheet" href="stylesenha.css">
   
</head>
<body>
  <div class="container">
    <h1>Recuperar Senha</h1>

    <?php if (!empty($mensagem)): ?>
      <p><?php echo $mensagem; ?></p>
    <?php endif; ?>

    <form id="usuarioLogin" action="recuperar-senha.php" method="post">
      <input type="text" id="usuario" name="usuario" placeholder="Digite seu nome de usuário" required>
      <input type="password" id="senha" name="senha" placeholder="Digite sua nova senha" required>
      <button type="submit">Recuperar Senha</button>
      <button type="button" id="sair" name="sair" onclick="window.location.href='index.php'">Sair</button>
    </form>
  </div>

  <script src="resetarsenha.js"></script>
</body>
</html>