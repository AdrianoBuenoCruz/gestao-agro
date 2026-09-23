# 🌾 Gestor Agro

**Sistema web para gestão agrícola**, desenvolvido por Adriano Bueno como projeto do Bacharelado em Sistemas de Informação da Universidade Estadual de Goiás (UEG).

O Gestor Agro reúne informações da propriedade em um painel para acompanhar a produção, as finanças, os equipamentos e as atividades da equipe.

## Visão do sistema

![Painel geral do Gestor Agro](PainelGeral.png)

O painel apresenta indicadores como saldo financeiro, quantidade e área colhida, operações ativas, colaboradores ativos e equipamentos em manutenção.

## Funcionalidades

| Área | O que permite acompanhar |
| --- | --- |
| 🌱 **Safras e produção** | Culturas, quantidade produzida e área cultivada ou colhida. |
| 💰 **Financeiro** | Receitas, despesas, lançamentos e formas de pagamento. |
| 🚜 **Equipamentos** | Cadastro e situação de tratores, colheitadeiras, plantadeiras e pulverizadores. |
| ⚙️ **Operações e manutenção** | Atividades com máquinas, operadores e registros de manutenção. |
| 👥 **Colaboradores e usuários** | Equipe, perfis de acesso e permissões por função. |
| 📊 **Indicadores** | Resumos visuais da produção e das finanças da propriedade. |

## Tecnologias

**PHP, PDO, MySQL, HTML, CSS, JavaScript e Bootstrap.** O projeto combina classes PHP existentes com módulos organizados em `app/Core`, `app/Models`, `app/Controllers` e `app/Views`.

## Executar localmente

1. Tenha PHP 8 e MySQL disponíveis em seu ambiente.
2. Crie um banco de dados e importe `database.sql` para as tabelas iniciais.
3. Execute `database_atualizacao_modulos.sql` no mesmo banco para acrescentar usuários, colaboradores, operações, manutenções e dados da fazenda.
4. Copie `conexao.example.php` para `conexao.php` e preencha as credenciais do seu banco. O arquivo `conexao.php` é ignorado pelo Git.
5. Sirva a pasta do projeto em um ambiente PHP com suporte a `.htaccess` e abra `index.php`.

O SQL disponibilizado contém a estrutura inicial e a atualização incremental. Ele não inclui dados da propriedade nem credenciais de acesso. As configurações de serviços externos, quando usadas, devem ser ajustadas no ambiente de execução.

## Estado do projeto

A aplicação possui uma **versão funcional**. A documentação acadêmica do TCC está em revisão para apresentação.

O repositório contém o código-fonte e uma imagem do painel. Os arquivos privados de conexão, os logs do servidor e os backups não são publicados.

## Autor

**Adriano Bueno** · [GitHub](https://github.com/AdrianoBuenoCruz)

Bacharelado em Sistemas de Informação · UEG
