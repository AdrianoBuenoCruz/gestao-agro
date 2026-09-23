<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Input;
use PDO;

final class Fazenda
{
    public function __construct(private PDO $pdo) {}

    public function obter(): array
    {
        $result = $this->pdo->query('SELECT * FROM fazenda_config WHERE id=1')->fetch(PDO::FETCH_ASSOC);
        return $result ?: [
            'id' => 1, 'nome' => 'FN Agropecuária', 'area_total_ha' => 0,
            'area_produtiva_ha' => 0, 'municipio' => 'Itaberaí', 'estado' => 'GO',
            'cultura_principal' => null,
        ];
    }

    public function salvar(array $dados): void
    {
        $nome = Input::string($dados, 'nome', 150);
        if ($nome === '') {
            throw new \InvalidArgumentException('Informe o nome da fazenda.');
        }
        $areaTotal = Input::nullableFloat($dados, 'area_total_ha') ?? 0;
        $areaProdutiva = Input::nullableFloat($dados, 'area_produtiva_ha') ?? 0;
        if ($areaTotal < 0 || $areaProdutiva < 0 || ($areaTotal > 0 && $areaProdutiva > $areaTotal)) {
            throw new \InvalidArgumentException('A área produtiva não pode ser maior que a área total.');
        }
        $stmt = $this->pdo->prepare(
            'INSERT INTO fazenda_config
             (id,nome,area_total_ha,area_produtiva_ha,municipio,estado,cultura_principal)
             VALUES (1,:nome,:total,:produtiva,:municipio,:estado,:cultura)
             ON DUPLICATE KEY UPDATE nome=VALUES(nome), area_total_ha=VALUES(area_total_ha),
             area_produtiva_ha=VALUES(area_produtiva_ha), municipio=VALUES(municipio),
             estado=VALUES(estado), cultura_principal=VALUES(cultura_principal)'
        );
        $stmt->execute([
            ':nome' => $nome,
            ':total' => $areaTotal,
            ':produtiva' => $areaProdutiva,
            ':municipio' => Input::nullableString($dados, 'municipio', 100),
            ':estado' => strtoupper(Input::string($dados, 'estado', 2)),
            ':cultura' => Input::nullableString($dados, 'cultura_principal', 100),
        ]);
    }
}
