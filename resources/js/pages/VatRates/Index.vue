<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

import AppLayout from '@/layouts/AppLayout.vue'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

/* -------------------------------------------------------------------------- */
/* TYPES */
/* -------------------------------------------------------------------------- */

interface VatRate {
    id: number
    name: string
    rate: number
    description?: string
    is_default: boolean
    articles_count: number
}

const { rates } = defineProps<{
    rates: VatRate[]
}>()

/* -------------------------------------------------------------------------- */
/* STATE */
/* -------------------------------------------------------------------------- */

const isEditing = ref(false)
const editingId = ref<number | null>(null)

/* -------------------------------------------------------------------------- */
/* FORM */
/* -------------------------------------------------------------------------- */

const form = useForm({
    name: '',
    rate: 23,
    description: '',
    is_default: false,
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/vat-rates/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/vat-rates', {
            onSuccess: clearForm,
        })
    }
}

function destroy(rate: VatRate) {
    if (rate.articles_count > 0) {
        alert(`Não é possível eliminar. Existem ${rate.articles_count} artigo(s) com esta taxa.`)
        return
    }

    if (confirm(`Tem certeza que deseja eliminar a taxa "${rate.name}"?`)) {
        router.delete(`/vat-rates/${rate.id}`, {
            preserveScroll: true,
        })
    }
}

function clearForm() {
    isEditing.value = false
    editingId.value = null
    form.reset()
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Configurações - Taxas IVA
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Nova' }} Taxa IVA</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Nome *</Label>
                                    <Input id="name" v-model="form.name" placeholder="Ex: IVA 23%" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="rate">Taxa (%) *</Label>
                                    <Input id="rate" v-model.number="form.rate" type="number" step="0.01" min="0"
                                        max="100" placeholder="23.00" />
                                    <p v-if="form.errors.rate" class="text-sm text-destructive">
                                        {{ form.errors.rate }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Descrição</Label>
                                <Input id="description" v-model="form.description" placeholder="Ex: Taxa normal" />
                            </div>

                            <div class="flex items-center gap-2">
                                <Checkbox id="is_default" :checked="form.is_default"
                                    @update:checked="(value: boolean) => form.is_default = value" />
                                <Label for="is_default">Taxa por defeito</Label>
                            </div>

                            <div class="flex gap-2">
                                <Button type="submit" :disabled="form.processing">
                                    {{ isEditing ? 'Atualizar' : 'Guardar' }}
                                </Button>
                                <Button type="button" variant="outline" @click="clearForm">
                                    Limpar
                                </Button>
                            </div>

                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Taxas IVA</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Taxa</TableHead>
                                    <TableHead>Descrição</TableHead>
                                    <TableHead>Artigos</TableHead>
                                    <TableHead>Default</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="rate in rates" :key="rate.id">
                                    <TableCell class="font-medium">{{ rate.name }}</TableCell>
                                    <TableCell>{{ rate.rate }}%</TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ rate.description || '-' }}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ rate.articles_count }} artigo(s)
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <span v-if="rate.is_default"
                                            class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                            Default
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="destructive" @click="destroy(rate)"
                                                :disabled="rate.articles_count > 0">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="rates.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        Nenhuma taxa encontrada.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

            </div>
        </div>
    </AppLayout>
</template>