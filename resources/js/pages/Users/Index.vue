<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
} from '@/components/ui/select'
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

interface User {
    id: number
    name: string
    email: string
    mobile?: string
    permission_group?: string
    status: string
}

/* -------------------------------------------------------------------------- */
/* PROPS */
/* -------------------------------------------------------------------------- */

const { users } = defineProps<{
    users: User[]
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
    email: '',
    mobile: '',
    permission_group: '',
    status: 'active',
    password: '',
})

/* -------------------------------------------------------------------------- */
/* ACTIONS */
/* -------------------------------------------------------------------------- */

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/users/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/users', {
            onSuccess: clearForm,
        })
    }
}

function edit(user: User) {
    isEditing.value = true
    editingId.value = user.id

    form.name = user.name
    form.email = user.email
    form.mobile = user.mobile ?? ''
    form.permission_group = user.permission_group ?? ''
    form.status = user.status
    form.password = ''

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(user: User) {
    if (confirm('Tem certeza que deseja eliminar este utilizador?')) {
        router.delete(`/users/${user.id}`, {
            preserveScroll: true,
        })
    }
}

function clearForm() {
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.status = 'active'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestão de Acessos - Utilizadores
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEditing ? 'Editar' : 'Novo' }} Utilizador</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="name">Nome *</Label>
                                    <Input id="name" v-model="form.name" />
                                    <p v-if="form.errors.name" class="text-sm text-destructive">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email *</Label>
                                    <Input id="email" v-model="form.email" type="email" />
                                    <p v-if="form.errors.email" class="text-sm text-destructive">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="mobile">Telemóvel</Label>
                                        <Input id="mobile" v-model="form.mobile" />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="permission_group">Grupo de Permissões</Label>
                                        <Input id="permission_group" v-model="form.permission_group"
                                            placeholder="Admin, Editor, Viewer..." />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="password">
                                        Password {{ isEditing ? '(deixe em branco para manter)' : '*' }}
                                    </Label>
                                    <Input id="password" v-model="form.password" type="password"
                                        :placeholder="isEditing ? 'Deixe em branco para não alterar' : 'Mínimo 8 caracteres'" />
                                    <p v-if="form.errors.password" class="text-sm text-destructive">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label>Estado *</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger />
                                        <SelectContent>
                                            <SelectItem value="active">Ativo</SelectItem>
                                            <SelectItem value="inactive">Inativo</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div class="flex gap-2">
                                    <Button type="submit" :disabled="form.processing">
                                        {{ isEditing ? 'Atualizar' : 'Guardar' }}
                                    </Button>
                                    <Button type="button" variant="outline" @click="clearForm">
                                        Limpar
                                    </Button>
                                </div>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Utilizadores</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Telemóvel</TableHead>
                                    <TableHead>Grupo Permissões</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="user in users" :key="user.id">
                                    <TableCell class="font-medium">{{ user.name }}</TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>{{ user.mobile }}</TableCell>
                                    <TableCell>
                                        <span v-if="user.permission_group"
                                            class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                            {{ user.permission_group }}
                                        </span>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <span :class="[
                                            'rounded-full px-2 py-1 text-xs',
                                            user.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        ]">
                                            {{ user.status === 'active' ? 'Ativo' : 'Inativo' }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(user)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(user)">
                                                Apagar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="users.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        Nenhum utilizador encontrado.
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