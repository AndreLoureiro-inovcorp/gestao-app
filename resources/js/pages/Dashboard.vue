<script setup lang="ts">
import { computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Users, FileText } from 'lucide-vue-next'

import type { BreadcrumbItem } from '@/types'

/* -------------------------------------------------------------------------- */
/* PAGE */
/* -------------------------------------------------------------------------- */

const page = usePage()

/* -------------------------------------------------------------------------- */
/* COMPUTED (SAFE) */
/* -------------------------------------------------------------------------- */

const subscription = computed(() => {
    return (page.props as any).subscription ?? null
})

const currentTenantName = computed(() => {
    return (page.props as any).auth?.user?.current_tenant?.name ?? ''
})

/* -------------------------------------------------------------------------- */
/* BREADCRUMBS */
/* -------------------------------------------------------------------------- */

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

/* -------------------------------------------------------------------------- */
/* COMPUTED VALUES */
/* -------------------------------------------------------------------------- */

const planColor = computed(() => {
    const slug = subscription.value?.plan?.slug
    if (slug === 'free') return 'secondary'
    return 'default'
})
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4">

            <!-- TOP CARDS -->
            <div class="grid gap-4 md:grid-cols-3">

                <!-- PLAN -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium">Plano Atual</CardTitle>
                            <Badge :variant="planColor">
                                {{ subscription?.plan?.name ?? '-' }}
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-2">
                        <div v-if="subscription?.trial?.active" class="text-sm text-muted-foreground">
                            🎁 Trial: {{ subscription.trial.days_remaining }} dias restantes
                        </div>

                        <Button size="sm" variant="outline" class="w-full">
                            Fazer Upgrade
                        </Button>
                    </CardContent>
                </Card>

                <!-- USERS -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium">Utilizadores</CardTitle>
                            <Users class="h-4 w-4 text-muted-foreground" />
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-2">
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold">
                                {{ subscription?.usage?.users ?? 0 }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                /
                                {{
                                    subscription?.limits?.users === 'unlimited'
                                        ? '∞'
                                        : subscription?.limits?.users ?? '-'
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- PROPOSALS -->
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm font-medium">Propostas</CardTitle>
                            <FileText class="h-4 w-4 text-muted-foreground" />
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-2">
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold">
                                {{ subscription?.usage?.proposals ?? 0 }}
                            </span>
                            <span class="text-sm text-muted-foreground">
                                /
                                {{
                                    subscription?.limits?.proposals === 'unlimited'
                                        ? '∞'
                                        : subscription?.limits?.proposals ?? '-'
                                }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- WELCOME -->
            <Card class="min-h-[50vh]">
                <CardHeader>
                    <CardTitle>Bem-vindo ao Dashboard</CardTitle>
                    <CardDescription>
                        Gerencie o seu tenant {{ currentTenantName }}
                    </CardDescription>
                </CardHeader>
            </Card>

        </div>
    </AppLayout>
</template>
