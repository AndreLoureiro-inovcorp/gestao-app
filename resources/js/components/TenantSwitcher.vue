<script setup lang="ts">
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import {
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
} from '@/components/ui/sidebar'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Building2, ChevronsUpDown, Check } from 'lucide-vue-next'
import type { PageProps } from '@inertiajs/core'

interface Tenant {
    id: number
    name: string
    role: string
}

interface User {
    id: number
    name: string
    email: string
    current_tenant?: {
        id: number
        name: string
    }
    tenants?: Tenant[]
}

const page = usePage<{ auth: { user: User | null } } & PageProps>()

const currentTenant = computed(() => page.props.auth.user?.current_tenant)
const tenants = computed(() => page.props.auth.user?.tenants || [])

function switchTenant(tenantId: number) {
    router.post(`/tenants/switch/${tenantId}`, {}, {
        preserveScroll: true,
    })
}
</script>

<template>
    <SidebarMenu v-if="currentTenant">
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton size="lg"
                        class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground">
                        <div
                            class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                            <Building2 class="size-4" />
                        </div>
                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-semibold">{{ currentTenant.name }}</span>
                            <span class="truncate text-xs">Tenant ativo</span>
                        </div>
                        <ChevronsUpDown class="ml-auto" />
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent class="w-64" align="start" side="bottom">
                    <DropdownMenuItem v-for="tenant in tenants" :key="tenant.id" class="cursor-pointer gap-2"
                        @click="switchTenant(tenant.id)">
                        <div class="flex size-6 items-center justify-center rounded-sm border">
                            <Building2 class="size-4" />
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">{{ tenant.name }}</div>
                            <div class="text-xs text-muted-foreground">{{ tenant.role }}</div>
                        </div>
                        <Check v-if="tenant.id === currentTenant.id" class="size-4" />
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>