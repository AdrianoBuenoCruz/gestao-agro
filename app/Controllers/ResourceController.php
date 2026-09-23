<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Input;
use App\Core\JsonResponse;
use App\Models\Colaborador;
use App\Models\Dashboard;
use App\Models\Fazenda;
use App\Models\Manutencao;
use App\Models\Operacao;
use App\Models\Usuario;
use PDO;

final class ResourceController
{
    public function __construct(private PDO $pdo) {}

    public function dispatch(string $resource): void
    {
        $action = Input::string($_REQUEST, 'action', 30) ?: 'listar';
        try {
            switch ($resource) {
                case 'colaboradores': $this->colaboradores($action); break;
                case 'usuarios': $this->usuarios($action); break;
                case 'operacoes': $this->operacoes($action); break;
                case 'manutencoes': $this->manutencoes($action); break;
                case 'fazenda': $this->fazenda($action); break;
                case 'dashboard': $this->dashboard(); break;
                default: JsonResponse::error('Recurso não encontrado.', 404);
            }
        } catch (\InvalidArgumentException $e) {
            JsonResponse::error($e->getMessage());
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            JsonResponse::error('Não foi possível concluir a operação no banco de dados.', 500);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            JsonResponse::error('Ocorreu um erro inesperado.', 500);
        }
    }

    private function colaboradores(string $action): void
    {
        $model = new Colaborador($this->pdo);
        if ($action === 'opcoes') {
            Auth::requireJson('operacoes.registrar');
            $dados = array_map(
                static fn(array $item): array => ['id' => $item['id'], 'nome' => $item['nome'], 'cargo' => $item['cargo']],
                $model->listar(true)
            );
            JsonResponse::send(['status' => true, 'dados' => $dados]);
        }
        Auth::requireJson($action === 'listar' ? 'colaboradores.ver' : 'colaboradores.gerenciar');
        if ($action === 'listar') {
            JsonResponse::send(['status' => true, 'dados' => $model->listar(!empty($_GET['ativos']))]);
        }
        Auth::validateCsrf();
        if ($action === 'salvar') {
            $id = $model->salvar($_POST);
            JsonResponse::send(['status' => true, 'mensagem' => 'Colaborador salvo com sucesso.', 'id' => $id]);
        }
        if ($action === 'excluir') {
            $model->excluir(Input::int($_POST, 'id'));
            JsonResponse::send(['status' => true, 'mensagem' => 'Colaborador excluído.']);
        }
        JsonResponse::error('Ação inválida.', 404);
    }

    private function usuarios(string $action): void
    {
        Auth::requireJson('usuarios.gerenciar');
        $model = new Usuario($this->pdo);
        if ($action === 'listar') {
            JsonResponse::send(['status' => true, 'dados' => $model->listar()]);
        }
        Auth::validateCsrf();
        if ($action === 'salvar') {
            $idSolicitado = Input::int($_POST, 'id');
            if ($idSolicitado === (int) $_SESSION['usuario_id']) {
                $_POST['ativo'] = '1';
            }
            $id = $model->salvar($_POST);
            if ($id === (int) $_SESSION['usuario_id']) {
                $_SESSION['nome'] = Input::string($_POST, 'nome', 120);
                $_SESSION['usuario'] = Input::string($_POST, 'usuario', 80);
                $_SESSION['nivel'] = Input::string($_POST, 'nivel', 40);
            }
            JsonResponse::send(['status' => true, 'mensagem' => 'Usuário salvo com sucesso.', 'id' => $id]);
        }
        if ($action === 'excluir') {
            $model->excluir(Input::int($_POST, 'id'), (int) $_SESSION['usuario_id']);
            JsonResponse::send(['status' => true, 'mensagem' => 'Usuário excluído.']);
        }
        JsonResponse::error('Ação inválida.', 404);
    }

    private function operacoes(string $action): void
    {
        $permission = $action === 'listar' ? 'operacoes.ver' : 'operacoes.registrar';
        Auth::requireJson($permission);
        $model = new Operacao($this->pdo);
        if ($action === 'listar') {
            JsonResponse::send(['status' => true, 'dados' => $model->listar()]);
        }
        Auth::validateCsrf();
        if ($action === 'salvar') {
            $id = $model->salvar($_POST);
            JsonResponse::send(['status' => true, 'mensagem' => 'Operação salva com sucesso.', 'id' => $id]);
        }
        if ($action === 'excluir') {
            Auth::requireJson('operacoes.gerenciar');
            $model->excluir(Input::int($_POST, 'id'));
            JsonResponse::send(['status' => true, 'mensagem' => 'Operação excluída.']);
        }
        JsonResponse::error('Ação inválida.', 404);
    }

    private function manutencoes(string $action): void
    {
        $permission = $action === 'listar' ? 'manutencoes.ver' : 'manutencoes.registrar';
        Auth::requireJson($permission);
        $model = new Manutencao($this->pdo);
        if ($action === 'listar') {
            JsonResponse::send(['status' => true, 'dados' => $model->listar()]);
        }
        Auth::validateCsrf();
        if ($action === 'salvar') {
            $id = $model->salvar($_POST);
            JsonResponse::send(['status' => true, 'mensagem' => 'Manutenção salva com sucesso.', 'id' => $id]);
        }
        if ($action === 'excluir') {
            Auth::requireJson('manutencoes.gerenciar');
            $model->excluir(Input::int($_POST, 'id'));
            JsonResponse::send(['status' => true, 'mensagem' => 'Manutenção excluída.']);
        }
        JsonResponse::error('Ação inválida.', 404);
    }

    private function fazenda(string $action): void
    {
        Auth::requireJson($action === 'obter' ? 'dashboard.ver' : 'fazenda.gerenciar');
        $model = new Fazenda($this->pdo);
        if ($action === 'obter') {
            JsonResponse::send(['status' => true, 'dados' => $model->obter()]);
        }
        Auth::validateCsrf();
        if ($action === 'salvar') {
            $model->salvar($_POST);
            JsonResponse::send(['status' => true, 'mensagem' => 'Dados da fazenda atualizados.']);
        }
        JsonResponse::error('Ação inválida.', 404);
    }

    private function dashboard(): void
    {
        Auth::requireJson('dashboard.ver');
        $dados = (new Dashboard($this->pdo))->indicadores();
        $dados['fazenda'] = (new Fazenda($this->pdo))->obter();
        JsonResponse::send(['status' => true, 'dados' => $dados]);
    }
}
