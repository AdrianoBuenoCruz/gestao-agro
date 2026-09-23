<?php
session_start();
require_once 'conexao.php';

// Lógica de processamento de login
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($usuario) || empty($senha)) {
        $erro = "Preencha usuário e senha.";
    } else {
        $stmt = $pdo->prepare("
    SELECT * FROM usuarios
    WHERE (usuario = :login OR email = :login)
    AND ativo = 1
    LIMIT 1
");

$stmt->execute(['login' => $usuario]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dados && password_verify($senha, $dados['senha'])) {
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = $dados['id'];
    $_SESSION['usuario'] = $dados['usuario'];
    $_SESSION['nome'] = $dados['nome'];
    $_SESSION['nivel'] = $dados['nivel'];

    header("Location: modulos.php");
    exit;
} else {
    $erro = "Usuário/e-mail ou senha inválidos.";
}
    }
}

// ---- Busca o clima de Itaberaí-GO via Open-Meteo ----
// Fica FORA do bloco POST: precisa rodar em toda visita à página (GET e POST)
$clima = null;
$lat = -16.0206;
$lon = -49.8060;
$url_clima = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,weather_code&timezone=America%2FSao_Paulo";

$ctx = stream_context_create(['http' => ['timeout' => 3]]); // não deixa a tela de login travar se a API estiver fora
$resposta = @file_get_contents($url_clima, false, $ctx);

if ($resposta !== false) {
    $dados = json_decode($resposta, true);
    if (isset($dados['current'])) {
        $clima = [
            'temp' => round($dados['current']['temperature_2m']),
            'codigo' => $dados['current']['weather_code']
        ];
    }
}

// Traduz o weather_code da Open-Meteo pra um texto/ícone simples
function descreverClima($codigo) {
    $mapa = [
        0 => ['Céu limpo', '☀️'],
        1 => ['Poucas nuvens', '🌤️'],
        2 => ['Parcialmente nublado', '⛅'],
        3 => ['Nublado', '☁️'],
        45 => ['Neblina', '🌫️'], 48 => ['Neblina', '🌫️'],
        51 => ['Garoa leve', '🌦️'], 53 => ['Garoa', '🌦️'], 55 => ['Garoa forte', '🌦️'],
        61 => ['Chuva leve', '🌧️'], 63 => ['Chuva', '🌧️'], 65 => ['Chuva forte', '🌧️'],
        80 => ['Pancadas de chuva', '🌧️'], 81 => ['Pancadas de chuva', '🌧️'], 82 => ['Pancadas fortes', '⛈️'],
        95 => ['Tempestade', '⛈️'], 96 => ['Tempestade c/ granizo', '⛈️'], 99 => ['Tempestade c/ granizo', '⛈️'],
    ];
    return $mapa[$codigo] ?? ['Tempo estável', '🌤️'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gestor Agro </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="split-container">

        <div class="login-banner">
            <div class="banner-conteudo">

                <?php if ($clima):
                    [$descricao, $icone] = descreverClima($clima['codigo']);
                ?>
                    <div class="banner-clima">
                        <span class="clima-icone"><?php echo $icone; ?></span>
                        <div class="clima-info">
                            <strong><?php echo $clima['temp']; ?>°C</strong>
                            <span><?php echo $descricao; ?> · Itaberaí, GO</span>
                        </div>
                    </div>
                <?php endif; ?>

                <h2>Tecnologia e inteligência para facilitar a gestão no campo.</h2>
                <p>Com o Gestor Agro, você acompanha a produção, organiza as finanças e gerencia os equipamentos de forma simples e prática..</p>
            </div>
        </div>

        <div class="login-area">
            <div class="login-box">
                <h1>Entrar no Sistema</h1>
                <p class="descricao">Seja bem-vindo! Insira suas credenciais.</p>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger py-2 small mb-4"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>

                <form method="post" action="index.php">
                    <div class="campo">
                        <label>Usuário ou E-mail</label>
                        <input type="text" name="usuario" required autocomplete="off">
                    </div>
                    
                    <div class="campo">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="mb-0">Senha</label>
                            <a href="recuperar-senha.php" class="esqueci-senha">Esqueci a senha</a>
                        </div>
                        <input type="password" name="senha" required>
                    </div>

                    <div class="captcha-wrapper">
                        <div class="g-recaptcha" data-sitekey="6Ld5DX4tAAAAAEBIWCjuWgC_zmSFHwU9I9eXBlWC"></div>
                    </div>

                    <button type="submit" class="btn-entrar">Acessar Painel</button>
                </form>

                <div class="divisor">ou acesse com</div>

                <div class="d-flex justify-content-center">
                    <div id="g_id_onload"
                data-client_id="181768739305-271dfas2n6vpeehceqdut5nsono66020.apps.googleusercontent.com"
                         data-callback="handleCredentialResponse">
                    </div>
                    <div class="g_id_signin" data-type="standard" data-theme="outline" data-size="large" data-shape="rectangular"></div>
                </div>

                <div class="footer-login">
                    <p>© 2026 Gestor Agro · Todos os direitos reservados</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function handleCredentialResponse(response) {
            fetch("/gestao_agricola/google-login.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "credential=" + encodeURIComponent(response.credential)
            })
            .then(res => res.text())
            .then(data => {
                if (data.trim() === "sucesso") {
                    window.location.href = "/gestao_agricola/modulos.php";
                } else {
                    if (window.Swal) {
                        Swal.fire({ title: "Não foi possível entrar", text: data, icon: "error", confirmButtonColor: "#3F6B47" });
                    } else {
                        alert("Erro no login com Google: " + data);
                    }
                }
            })
            .catch(error => console.error("Erro:", error));
        }
    </script>
</body>
</html>
