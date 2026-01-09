<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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

interface Country {
    id: number
    name: string
    code: string
}

const props = defineProps<{
    countries: Country[]
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
    code: '',
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/countries/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/countries', {
            onSuccess: clearForm,
        })
    }
}

function edit(country: Country) {
    isEditing.value = true
    editingId.value = country.id

    form.name = country.name
    form.code = country.code

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(country: Country) {
    if (confirm('Tem certeza que deseja eliminar este país?')) {
        router.delete(`/countries/${country.id}`, {
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Configurações - Países
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Novo' }} País</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-4" @submit.prevent="submit">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Nome *</Label>
                                    <Input id="name" v-model="form.name" placeholder="Portugal" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="code">Código (ISO 2) *</Label>
                                    <Input id="code" v-model="form.code" placeholder="PT" maxlength="2"
                                        class="uppercase" />
                                    <p v-if="form.errors.code" class="text-sm text-destructive">
                                        {{ form.errors.code }}
                                    </p>
                                </div>
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
                        <CardTitle>Lista de Países</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Código</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="country in countries" :key="country.id">
                                    <TableCell class="font-medium">{{ country.name }}</TableCell>
                                    <TableCell>
                                        <span class="rounded bg-gray-100 px-2 py-1 text-xs font-mono">
                                            {{ country.code }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(country)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(country)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="countries.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground">
                                        Nenhum país encontrado.
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