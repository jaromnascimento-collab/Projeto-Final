# 🚗 AutoList - Sistema de Gestão de Clientes para Loja de Veículos

Sistema completo de captação e gestão de clientes para lojas de veículos, com integração à API FIPE e sistema de autenticação.

## ✨ Funcionalidades

### 🔐 Sistema de Autenticação
- Login seguro com usuário e senha
- Controle de sessão em todas as páginas
- Diferentes níveis de acesso (Admin/Vendedor)
- Logout automático

### 👥 Gestão de Clientes
- **Cadastro**: Captação de clientes interessados em veículos
- **Listagem**: Visualização completa do banco de clientes
- **Edição**: Atualização de dados pessoais e veículos
- **Exclusão**: Remoção segura de registros

### 🚙 Integração FIPE
- Consulta automática de valores de veículos
- Seleção dinâmica de marca, modelo e ano
- Dados atualizados da tabela FIPE
- Validação de informações antes do cadastro

### 📊 Dashboard
- Estatísticas em tempo real
- Contadores de clientes
- Interface moderna e responsiva
- Navegação intuitiva

## 🚀 Como Usar

### 1. Configuração Inicial
1. Configure o banco de dados MySQL
2. Importe o arquivo `conexao.php` com suas credenciais
3. Acesse `login.php` no navegador

### 2. Credenciais de Acesso
- **Admin**: `admin` / `admin123`
- **Vendedor**: `vendedor` / `vendedor123`

### 3. Fluxo de Trabalho
1. **Login** → Acesse o sistema
2. **Dashboard** → Visualize estatísticas
3. **Cadastrar** → Capture novos clientes
4. **Listar** → Gerencie clientes existentes
5. **Editar** → Atualize informações
6. **Excluir** → Remova registros

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Ícones**: Font Awesome 6
- **API**: Tabela FIPE (parallelum.com.br)

## 📁 Estrutura do Projeto

```
Projeto Autolist/
├── auth.php          # Sistema de autenticação
├── conexao.php       # Configuração do banco
├── login.php         # Página de login
├── logout.php        # Logout do sistema
├── index.php         # Dashboard principal
├── cadastro.php      # Captação de clientes
├── listar.php        # Listagem de clientes
├── editar.php        # Edição de registros
├── excluir.php       # Exclusão de registros
└── README.md         # Documentação
```

## 🎯 Foco em Vendas

O sistema foi desenvolvido especificamente para **lojas de veículos** com foco em:

- **Captação de Leads**: Capture clientes interessados
- **Banco de Dados**: Organize informações de contato
- **Follow-up**: Mantenha histórico de interesses
- **Oportunidades**: Identifique chances de venda
- **Relatórios**: Acompanhe performance

## 🔒 Segurança

- Prepared statements em todas as consultas
- Validação de entrada de dados
- Sanitização de saída
- Controle de sessão
- Proteção contra SQL injection

## 📱 Design Responsivo

- Interface adaptável para desktop e mobile
- Design moderno e profissional
- Navegação intuitiva
- Feedback visual para ações

## 🚀 Próximos Passos

- [ ] Sistema de relatórios avançados
- [ ] Integração com CRM
- [ ] Notificações por email
- [ ] Dashboard com gráficos
- [ ] Sistema de backup automático

---

**Desenvolvido para maximizar a conversão de leads em vendas!** 🎯

