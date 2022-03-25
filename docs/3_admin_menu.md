# Configure admin sidebar menu

```yaml
# config/packages/sfs_admin.yaml
twig:
    globals:
        admin_menu:
            _view: '@SfsAdmin/partials/sidebar-pills.html.twig'
            _translation_domain: 'sfs_admin'
            _active_for_routes_class: 'active'
            main:
                translation_key: 'sidebar.main'
                dashboard:
                    translation_key: 'sidebar.main.dashboard'
                    route: 'admin_dashboard'
                    role: ROLE_ADMIN_USERS_LIST
                    active_expression: 'admin'
            user:
                translation_key: 'sidebar.users'
                users:
                    translation_key: 'sidebar.users.users'
                    route: 'sfs_user_admin_users_list'
                    role: ROLE_ADMIN_USERS_LIST
                    active_expression: 'sfs_user_admin_users_'
                administrators:
                    translation_key: 'sidebar.users.administrators'
                    route: 'sfs_user_admin_administrators_list'
                    role: ROLE_ADMIN_ADMINISTRATORS_LIST
                    active_expression: 'sfs_user_admin_administrators_'
                invitations:
                    translation_key: 'sidebar.users.invitations'
                    route: 'sfs_user_admin_invitations_list'
                    role: ROLE_ADMIN_INVITATIONS_LIST
                    active_expression: 'sfs_user_admin_invitations_'
                history:
                    translation_key: 'sidebar.users.history'
                    route: 'sfs_user_admin_access_history_list'
                    role: ROLE_ADMIN_ACCESS_HISTORY_LIST
                    active_expression: 'sfs_user_admin_access_history_'
            mailer:
                translation_key: 'sidebar.mails'
                templates:
                    translation_key: 'sidebar.mails.templates'
                    route: 'sfs_mailer_templates_search'
                    role: ROLE_ADMIN_MAILER_TEMPLATES
                    active_expression: 'sfs_mailer_templates_'
                history:
                    translation_key: 'sidebar.mails.history'
                    route: 'sfs_mailer_history_search'
                    role: ROLE_ADMIN_MAILER_HISTORY
                    active_expression: 'sfs_mailer_history_'
```
    