# Guia de Atualização - Patient Management System v1.1

## Visão Geral da Atualização

A versão 1.1 do Patient Management System introduz uma funcionalidade revolucionária: **Portal do Paciente no Frontend**. Esta atualização permite que os pacientes acessem seus registros médicos diretamente através do site, sem necessidade de acesso à área administrativa.

## Principais Novidades

### 🆕 Portal do Paciente
- Acesso seguro aos registros médicos via frontend
- Posts/páginas protegidos por senha automaticamente gerados
- Interface responsiva e amigável ao usuário
- Shortcodes para fácil integração

### 🔒 Segurança Aprimorada
- Sistema de senhas único por paciente
- Verificação de nonce em todas as requisições
- Posts excluídos da busca pública
- Conformidade com LGPD

### 📱 Design Responsivo
- Otimizado para dispositivos móveis
- Suporte a modo escuro
- Interface moderna e intuitiva
- Funcionalidades de impressão e compartilhamento

## Pré-requisitos

Antes de atualizar, certifique-se de que:

- [ ] WordPress versão 5.0 ou superior
- [ ] PHP versão 7.4 ou superior
- [ ] Backup completo do site e banco de dados
- [ ] Acesso FTP ou painel de administração
- [ ] Plugins conflitantes desativados temporariamente

## Processo de Atualização

### Passo 1: Backup Completo

```bash
# Backup do banco de dados
mysqldump -u usuario -p nome_banco > backup_pms_$(date +%Y%m%d).sql

# Backup dos arquivos
tar -czf backup_site_$(date +%Y%m%d).tar.gz /caminho/para/wordpress/
```

### Passo 2: Desativar Plugin Atual

1. Acesse **Plugins > Plugins Instalados**
2. Localize "Patient Management System"
3. Clique em **Desativar**
4. **NÃO delete** o plugin ainda

### Passo 3: Instalar Nova Versão

#### Método 1: Upload Manual
1. Baixe o arquivo `patient-management-system-v1.1.0.zip`
2. Acesse **Plugins > Adicionar Novo > Enviar Plugin**
3. Selecione o arquivo e clique em **Instalar Agora**
4. **Não ative ainda**

#### Método 2: FTP
1. Extraia o arquivo ZIP
2. Faça upload da pasta para `/wp-content/plugins/`
3. Substitua a versão anterior quando solicitado

### Passo 4: Ativar Plugin Atualizado

1. Acesse **Plugins > Plugins Instalados**
2. Localize "Patient Management System v1.1"
3. Clique em **Ativar**
4. Aguarde a mensagem de sucesso

### Passo 5: Executar Migração de Dados

A migração é automática, mas você pode forçá-la:

1. Acesse qualquer registro médico existente
2. Faça uma pequena edição (adicione um espaço)
3. Clique em **Atualizar**
4. Repita para registros importantes

## Configuração Pós-Atualização

### Criar Página do Portal

1. Acesse **Páginas > Adicionar Nova**
2. Título: "Portal do Paciente"
3. Conteúdo: `[pms_patient_portal]`
4. Publique a página
5. Anote o URL para fornecer aos pacientes

### Configurar Menu de Navegação

1. Acesse **Aparência > Menus**
2. Adicione a página "Portal do Paciente"
3. Posicione no local desejado
4. Salve o menu

### Testar Funcionalidade

1. Crie um paciente de teste
2. Crie um registro médico para este paciente
3. Acesse a página do portal
4. Teste o login com as credenciais do paciente
5. Verifique se o registro é exibido corretamente

## Verificação de Compatibilidade

### Temas Testados
- ✅ Twenty Twenty-Three
- ✅ Twenty Twenty-Two
- ✅ Astra
- ✅ GeneratePress
- ✅ OceanWP

### Plugins Compatíveis
- ✅ Yoast SEO
- ✅ Contact Form 7
- ✅ WooCommerce
- ✅ Elementor
- ✅ Gutenberg

### Plugins Conflitantes Conhecidos
- ⚠️ Alguns plugins de cache podem interferir com AJAX
- ⚠️ Plugins de segurança muito restritivos podem bloquear funcionalidades
- ⚠️ Plugins de otimização que minificam CSS/JS agressivamente

## Resolução de Problemas

### Problema: Posts não são criados automaticamente

**Sintomas:**
- Registros médicos não geram páginas no frontend
- Pacientes não conseguem acessar registros

**Soluções:**
1. Verifique se o paciente tem ID válido
2. Confirme que a data de nascimento está preenchida
3. Edite e salve um registro para forçar criação
4. Verifique logs de erro do WordPress

### Problema: Portal não carrega corretamente

**Sintomas:**
- Página em branco ou erro 500
- Estilos não aplicados
- JavaScript não funciona

**Soluções:**
1. Verifique compatibilidade do tema
2. Desative outros plugins temporariamente
3. Aumente limite de memória PHP
4. Verifique permissões de arquivos

### Problema: Senhas não funcionam

**Sintomas:**
- Pacientes não conseguem acessar registros
- Erro de "senha incorreta"

**Soluções:**
1. Verifique formato da data de nascimento (AAAA-MM-DD)
2. Confirme que não há espaços extras nos dados
3. Regenere a senha editando o registro
4. Teste com um novo paciente

### Problema: Performance lenta

**Sintomas:**
- Portal carrega lentamente
- Timeouts em requisições AJAX

**Soluções:**
1. Configure cache de página
2. Otimize banco de dados
3. Aumente recursos do servidor
4. Ative compressão GZIP

## Configurações Avançadas

### Personalizar Geração de Senhas

Para modificar o padrão de senhas, edite o arquivo:
`/includes/class-pms-medical-records-manager.php`

Localize o método `generate_patient_password()` e modifique conforme necessário:

```php
private function generate_patient_password( $patient_id, $patient_dob ) {
    // Seu algoritmo personalizado aqui
    return 'sua_senha_personalizada';
}
```

### Customizar Aparência

Adicione CSS personalizado em **Aparência > Personalizar > CSS Adicional**:

```css
/* Personalizar cores do portal */
.pms-portal-header {
    background: linear-gradient(135deg, #sua-cor 0%, #sua-cor-2 100%);
}

/* Personalizar botões */
.pms-btn-primary {
    background: #sua-cor;
}
```

### Configurar Emails Automáticos

Para enviar credenciais por email automaticamente:

```php
// Adicione ao functions.php do tema
add_action('save_post_pms_medical_record', 'enviar_credenciais_paciente');

function enviar_credenciais_paciente($post_id) {
    // Sua lógica de email aqui
}
```

## Rollback (Se Necessário)

Se encontrar problemas críticos:

### Passo 1: Desativar Nova Versão
1. Acesse **Plugins > Plugins Instalados**
2. Desative "Patient Management System v1.1"

### Passo 2: Restaurar Versão Anterior
1. Restaure backup dos arquivos
2. Restaure backup do banco de dados
3. Reative a versão anterior

### Passo 3: Reportar Problema
1. Documente o erro encontrado
2. Inclua logs de erro
3. Entre em contato com suporte

## Suporte e Recursos

### Documentação
- [Documentação Completa](./documentation.md)
- [Guia do Usuário](./README.md)
- [Changelog](./CHANGELOG.md)

### Suporte Técnico
- **Email:** suporte@exemplo.com
- **Fórum:** https://forum.exemplo.com
- **Tickets:** https://suporte.exemplo.com

### Recursos Adicionais
- **Vídeos Tutoriais:** https://youtube.com/exemplo
- **Webinars:** Quintas-feiras às 14h
- **Comunidade:** https://facebook.com/groups/exemplo

## Checklist Final

Após a atualização, verifique:

- [ ] Plugin ativado com sucesso
- [ ] Página do portal criada e funcionando
- [ ] Registros existentes migrados
- [ ] Novos registros geram posts automaticamente
- [ ] Pacientes conseguem fazer login
- [ ] Interface responsiva funcionando
- [ ] Funcionalidades de impressão/compartilhamento operacionais
- [ ] Performance satisfatória
- [ ] Backup pós-atualização realizado

## Próximos Passos

1. **Treine sua equipe** nas novas funcionalidades
2. **Informe os pacientes** sobre o novo portal
3. **Configure monitoramento** de uso e performance
4. **Planeje melhorias** baseadas no feedback
5. **Mantenha-se atualizado** com futuras versões

---

**Importante:** Esta atualização introduz mudanças significativas. Teste em ambiente de desenvolvimento antes de aplicar em produção. Em caso de dúvidas, entre em contato com nossa equipe de suporte.

**Data de Lançamento:** Dezembro 2024  
**Versão:** 1.1.0  
**Compatibilidade:** WordPress 5.0+ | PHP 7.4+

