<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Check } from 'lucide-vue-next'

interface Plan {
    id: number
    name: string
    slug: string
    price: number
    limits: {
        users: number | string
        proposals: number | string
    }
    features: string[]
}

defineProps<{
    plans: Plan[]
    currentPlan: Plan | null
}>()

const changePlan = (planId: number) => {
    if (confirm('Tem certeza que deseja mudar de plano?')) {
        useForm({}).post(`/plans/${planId}`)
    }
}

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(price)
}
</script>

<template>

    <Head title="Planos" />

    <AppLayout>
        <div class="container mx-auto max-w-6xl py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold">Escolha o seu Plano</h1>
                <p class="text-muted-foreground">
                    Atualize o seu plano a qualquer momento
                </p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <Card v-for="plan in plans" :key="plan.id" :class="[
                    'relative',
                    currentPlan?.id === plan.id ? 'ring-2 ring-primary' : '',
                ]">
                    <Badge v-if="currentPlan?.id === plan.id" class="absolute right-4 top-4">
                        Plano Atual
                    </Badge>

                    <CardHeader>
                        <CardTitle class="text-2xl">{{ plan.name }}</CardTitle>
                        <CardDescription class="text-3xl font-bold">
                            {{ formatPrice(plan.price) }}
                            <span class="text-sm font-normal">/mês</span>
                        </CardDescription>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2">
                                <Check class="h-4 w-4 text-green-600" />
                                <span class="text-sm">
                                    {{
                                        plan.limits.users === 'unlimited'
                                            ? 'Utilizadores ilimitados'
                                            : `${plan.limits.users} utilizadores`
                                    }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <Check class="h-4 w-4 text-green-600" />
                                <span class="text-sm">
                                    {{
                                        plan.limits.proposals === 'unlimited'
                                            ? 'Propostas ilimitadas'
                                            : `${plan.limits.proposals} propostas`
                                    }}
                                </span>
                            </li>
                            <li v-for="feature in plan.features" :key="feature" class="flex items-center gap-2">
                                <Check class="h-4 w-4 text-green-600" />
                                <span class="text-sm">{{ feature }}</span>
                            </li>
                        </ul>

                        <Button v-if="currentPlan?.id !== plan.id" class="w-full" @click="changePlan(plan.id)">
                            {{
                                currentPlan && plan.price > currentPlan.price
                                    ? 'Fazer Upgrade'
                                    : 'Mudar para este plano'
                            }}
                        </Button>

                        <Button v-else class="w-full" variant="outline" disabled>
                            Plano Atual
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>