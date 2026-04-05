Com base nas regras atuais do sistema, a descrição dos perfis fica assim:

1. ADMIN_MASTER  
    Pode: acessar área administrativa completa (institucional, comercial, enterprise) e operar em qualquer UF.  
    Não pode: acessar área operacional na sessão atual (perfil admin força área admin).
    
2. ADMIN_ORGAO  
    Pode: acessar área administrativa completa e recursos enterprise (incluindo features, API, integrações, automações, SLA, suporte, assinatura digital e analytics), desde que o módulo esteja contratado.  
    Não pode: operar fora da UF de contexto do usuário.
    
3. FINANCEIRO  
    Pode: acessar área administrativa; no enterprise pode SLA, tickets e analytics.  
    Não pode: gerenciar features, API, integrações, automações e assinatura digital no enterprise.
    
4. SUPORTE  
    Pode: acessar área administrativa; no enterprise pode gerenciar praticamente tudo (API, integrações, automações, SLA, tickets, assinatura digital, analytics).  
    Não pode: operar fora da UF de contexto (exceto se fosse ADMIN_MASTER).
    
5. GESTOR  
    Pode: área operacional completa (incidentes, briefing, comando, período, diário, PLANCON com edição, expansão de desastres, inteligência, documentos upload/download, governança, relatórios avançados).  
    Não pode: acessar área administrativa.
    
6. COORDENADOR  
    Pode: praticamente igual ao GESTOR na operação (inclui comando, PLANCON com edição, governança, upload/download de documentos).  
    Não pode: acessar área administrativa.
    
7. ANALISTA  
    Pode: abrir incidentes, briefing, criar período, registrar diário, editar PLANCON, expansão de desastres, governança, upload/download documentos, inteligência e relatórios.  
    Não pode: gerenciar comando inicial do incidente.
    
8. OPERADOR  
    Pode: abrir incidentes, briefing, diário operacional, expansão de desastres, upload/download documentos, inteligência e relatórios.  
    Não pode: comando inicial, criar período operacional, editar PLANCON, acessar governança.
    
9. LEITOR  
    Pode: visualizar módulos operacionais liberados (incidentes, PLANCON leitura, inteligência, documentos leitura/download, relatórios).  
    Não pode: criar/editar incidentes, briefing, comando, período, diário, PLANCON, expansão, upload de documentos, governança.
    

10. CONVIDADO  
    Pode: autenticar/deslogar.  
    Não pode: acessar efetivamente área admin nem operacional (sem permissões operacionais válidas).
    

Regras gerais importantes:

- Além do perfil, o módulo precisa estar contratado/liberado na assinatura (ex.: PLANCON, DOCUMENTS, ENTERPRISE_CORE, etc.).
- Usuários admin (ADMIN_MASTER, ADMIN_ORGAO, FINANCEIRO, SUPORTE) entram na área admin; os demais entram na operational.
- Hoje as rotas admin usam validação de área (area.admin) e não bloqueio por módulo ADMIN em middleware.