<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import TenantSwitcher from '@/components/TenantSwitcher.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { dashboard } from '@/routes'
import type { NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import {
    BookOpen,
    Folder,
    LayoutGrid,
    Users,
    Building2,
    Contact,
    FileText,
    ShoppingCart,
    Settings,
    DollarSign,
    Calendar,
} from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Clientes',
        href: '/entities?type=client',
        icon: Users,
    },
    {
        title: 'Fornecedores',
        href: '/entities?type=supplier',
        icon: Building2,
    },
    {
        title: 'Contactos',
        href: '/contacts',
        icon: Contact,
    },
    {
        title: 'Propostas',
        href: '/proposals',
        icon: FileText,
    },
    {
        title: 'Calendário',
        href: '/calendar-events',
        icon: Calendar,
    },
    {
        title: 'Encomendas',
        icon: ShoppingCart,
        items: [
            {
                title: 'Clientes',
                href: '/client-orders',
            },
            {
                title: 'Fornecedores',
                href: '/supplier-orders',
            },
        ],
    },
    {
        title: 'Financeiro',
        icon: DollarSign,
        items: [
            {
                title: 'Faturas Fornecedor',
                href: '/supplier-invoices',
            },
        ],
    },
    {
        title: 'Gestão de Acessos',
        icon: Users,
        items: [
            {
                title: 'Utilizadores',
                href: '/users',
            },
        ],
    },
    {
        title: 'Configurações',
        icon: Settings,
        items: [
            {
                title: 'Países',
                href: '/countries',
            },
            {
                title: 'Funções',
                href: '/contact-roles',
            },
            {
                title: 'IVA',
                href: '/vat-rates',
            },
            {
                title: 'Artigos',
                href: '/articles',
            },
            {
                title: 'Empresa',
                href: '/settings/company',
            },
            {
                title: 'Logs',
                href: '/activity-logs',
            },
        ],
    },
]

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
]
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
            <TenantSwitcher />
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
