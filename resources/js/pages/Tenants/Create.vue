<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3'

import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

interface Plan {
    id: number
    name: string
    price: number
}

const props = defineProps<{
    plans: Plan[]
}>()

const form = useForm({
    name: '',
    slug: '',
    plan_id: props.plans[0]?.id ?? null,
})

const submit = () => {
    form.post('/tenants')
}

const formatPrice = (price: number) =>
    new Intl.NumberFormat('pt-PT', {
        style: 'currency',
        currency: 'EUR',
    }).format(price)
</script>


<template>
    <AppLayout>

        <Head title="Criar Novo Tenant" />

        <div class="container mx-auto max-w-4xl py-8">
            <Card>
                <CardHeader>
                    <CardTitle>Criar Nova Empresa</CardTitle>
                    <CardDescription>
                        Configure uma nova empresa para começar a utilizar o sistema
                    </CardDescription>
                </CardHeader>

                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <!-- Informação Básica -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Informação Básica</h3>

                            <div class="space-y-4">
                                <div>
                                    <Label for="name">Nome da Empresa *</Label>
                                    <Input id="name" v-model="form.name" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <Label for="slug">Slug</Label>
                                    <Input id="slug" v-model="form.slug" />
                                </div>
                            </div>
                        </div>

                        <!-- Plano -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold">Plano</h3>

                            <Select v-model="form.plan_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Escolha um plano" />
                                </SelectTrigger>

                                <SelectContent>
                                    <SelectItem v-for="plan in plans" :key="plan.id" :value="plan.id">
                                        {{ plan.name }} — {{ formatPrice(plan.price) }}/mês
                                    </SelectItem>
                                </SelectContent>
                            </Select>

                            <p v-if="form.errors.plan_id" class="text-sm text-destructive">
                                {{ form.errors.plan_id }}
                            </p>
                        </div>

                        <!-- Botões -->
                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'A criar…' : 'Criar Empresa' }}
                            </Button>

                            <Button type="button" variant="outline" @click="router.visit('/dashboard')">
                                Cancelar
                            </Button>

                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
