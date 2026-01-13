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

interface ContactRole {
    id: number
    name: string
    contacts_count: number
}

const { roles } = defineProps<{
    roles: ContactRole[]
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
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/contact-roles/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/contact-roles', {
            onSuccess: clearForm,
        })
    }
}

function edit(role: ContactRole) {
    isEditing.value = true
    editingId.value = role.id
    form.name = role.name

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(role: ContactRole) {
    if (role.contacts_count > 0) {
        alert(`Não é possível eliminar. Existem ${role.contacts_count} contacto(s) com esta função.`)
        return
    }

    if (confirm(`Tem certeza que deseja eliminar a função "${role.name}"?`)) {
        router.delete(`/contact-roles/${role.id}`, {
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
                Configurações - Funções de Contactos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Nova' }} Função</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">

                            <div class="space-y-2">
                                <Label for="name">Nome da Função *</Label>
                                <Input id="name" v-model="form.name"
                                    placeholder="Ex: CEO, Diretor Comercial, Gestor..." />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
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
                        <CardTitle>Lista de Funções</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Contactos</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="role in roles" :key="role.id">
                                    <TableCell class="font-medium">{{ role.name }}</TableCell>
                                    <TableCell>
                                        <span class="text-sm text-muted-foreground">
                                            {{ role.contacts_count }} contacto(s)
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(role)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(role)"
                                                :disabled="role.contacts_count > 0">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="roles.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground">
                                        Nenhuma função encontrada.
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