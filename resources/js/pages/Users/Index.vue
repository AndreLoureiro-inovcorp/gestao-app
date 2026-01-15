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
    SelectValue,
} from '@/components/ui/select'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

interface User {
    id: number
    name: string
    email: string
    mobile?: string
    role: string // ✅ ALTERAR: role em vez de permission_group
    status: string
    joined_at?: string
}

const { users } = defineProps<{
    users: User[]
}>()

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = useForm({
    name: '',
    email: '',
    mobile: '',
    role: 'member', // ✅ ALTERAR: role em vez de permission_group
    status: 'active',
    password: '',
})

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
    form.role = user.role // ✅ ALTERAR
    form.status = user.status
    form.password = ''

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function destroy(user: User) {
    if (confirm('Tem certeza que deseja remover este utilizador do tenant?')) {
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
    form.role = 'member'
}

// ✅ NOVO: Helper para traduzir roles
function getRoleLabel(role: string): string {
    const labels: Record<string, string> = {
        owner: 'Proprietário',
        admin: 'Administrador',
        member: 'Membro'
    }
    return labels[role] || role
}

// ✅ NOVO: Helper para cor do badge
function getRoleBadgeClass(role: string): string {
    const classes: Record<string, string> = {
        owner: 'bg-purple-100 text-purple-800',
        admin: 'bg-blue-100 text-blue-800',
        member: 'bg-gray-100 text-gray-800'
    }
    return classes[role] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestão de Utilizadores
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

                                <div class="space-y-2">
                                    <Label for="mobile">Telemóvel</Label>
                                    <Input id="mobile" v-model="form.mobile" />
                                </div>

                                <!-- ✅ ALTERAR: Role em vez de permission_group -->
                                <div class="space-y-2">
                                    <Label for="role">Papel no Tenant *</Label>
                                    <Select v-model="form.role">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="owner">Proprietário</SelectItem>
                                            <SelectItem value="admin">Administrador</SelectItem>
                                            <SelectItem value="member">Membro</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.role" class="text-sm text-destructive">
                                        {{ form.errors.role }}
                                    </p>
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
                                    <Label for="status">Estado *</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Ativo</SelectItem>
                                            <SelectItem value="inactive">Inativo</SelectItem>
                                        </SelectContent>
                                    </Select>
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
                        <CardTitle>Utilizadores do Tenant</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Telemóvel</TableHead>
                                    <TableHead>Papel</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="user in users" :key="user.id">
                                    <TableCell class="font-medium">{{ user.name }}</TableCell>
                                    <TableCell>{{ user.email }}</TableCell>
                                    <TableCell>{{ user.mobile || '-' }}</TableCell>
                                    <TableCell>
                                        <!-- ✅ NOVO: Badge com role -->
                                        <Badge :class="getRoleBadgeClass(user.role)">
                                            {{ getRoleLabel(user.role) }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :class="[
                                            user.status === 'active'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-gray-100 text-gray-800'
                                        ]">
                                            {{ user.status === 'active' ? 'Ativo' : 'Inativo' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(user)">
                                                Editar
                                            </Button>
                                            <Button size="sm" variant="destructive" @click="destroy(user)">
                                                Remover
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="users.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground">
                                        Nenhum utilizador neste tenant.
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