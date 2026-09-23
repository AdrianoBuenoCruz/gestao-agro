# 🌾 Gestor Agro

Sistema web de gestão agrícola desenvolvido por **Adriano Bueno** como projeto do Bacharelado em Sistemas de Informação da **Universidade Estadual de Goiás (UEG)**.

O projeto reúne dados de safras, produção, finanças, equipamentos e equipe em um painel para apoiar o acompanhamento de uma propriedade rural.

## Visão do sistema

![Painel geral do Gestor Agro](PainelGeral.png)

O painel apresenta indicadores financeiros, área e quantidade colhida, operações, colaboradores e equipamentos em manutenção.

## Funcionalidades

| Área | O que o sistema registra ou acompanha |
| --- | --- |
| 🌱 **Safras e produção** | Culturas, área cultivada, colheitas e produtividade. |
| 💰 **Financeiro** | Receitas, despesas, categorias e formas de pagamento. |
| 🚜 **Equipamentos** | Cadastro, situação e manutenção de máquinas. |
| ⚙️ **Operações** | Atividades, operadores, horários e equipamentos utilizados. |
| 👥 **Equipe e acesso** | Colaboradores, usuários e permissões por função. |
| 📊 **Painel** | Resumos da produção e das finanças da propriedade. |

## Tecnologias

PHP 8, PDO, MySQL, HTML, CSS, JavaScript e Bootstrap. O código inclui classes PHP em `classes/` e módulos organizados em `app/Core`, `app/Models`, `app/Controllers` e `app/Views`.

## Executar localmente

**Pré-requisitos:** PHP 8 com extensões `pdo_mysql` e `mbstring`, MySQL e um servidor Apache configurado para executar PHP e ler `.htaccess` (por exemplo, um ambiente local com phpMyAdmin).

1. Crie um banco de dados MySQL vazio com codificação `utf8mb4`.
2. Importe `database.sql` nesse banco e, em seguida, `database_atualizacao_modulos.sql`. A segunda etapa cria, entre outras, a tabela de usuários.
3. Copie `conexao.example.php` para `conexao.php` e ajuste nome do banco, usuário e senha. Mantenha `conexao.php` fora do Git.
4. Crie um usuário inicial na tabela `usuarios` para conseguir entrar. O projeto **não fornece conta nem senha padrão**. Gere o hash da senha no terminal, na sua máquina:

   ~~~bash
   php -r '$senha = readline("Senha inicial: "); echo password_hash($senha, PASSWORD_DEFAULT), PHP_EOL;'
   ~~~

   Depois, no phpMyAdmin, insira um registro na tabela `usuarios` com `nome`, `usuario`, `senha` (o hash gerado), `nivel` = `Administrador` e `ativo` = `1`. Escolha um nome de usuário próprio e uma senha forte; não coloque a senha em texto puro no banco.
5. Abra `index.php` pelo endereço local do Apache e entre com esse usuário.

Os arquivos SQL contêm estrutura e dados iniciais de configuração da fazenda, sem dados reais de uma propriedade ou credenciais. A página de login consulta a Open-Meteo para exibir o clima; se a consulta falhar, o painel de clima pode não aparecer.

## Estado do projeto

Este repositório publica o **protótipo acadêmico** e a imagem do painel. A documentação do TCC está em revisão. As integrações de login com Google e recuperação de senha presentes no código ainda precisam de ajustes antes de qualquer implantação pública. Faça a avaliação e configuração de segurança adequadas ao seu ambiente antes de disponibilizar o sistema na internet.

Arquivos locais de conexão, logs e backups não fazem parte do repositório.

## Autor

**Adriano Bueno** · [GitHub](https://github.com/AdrianoBuenoCruz)

Bacharelado em Sistemas de Informação · UEG
