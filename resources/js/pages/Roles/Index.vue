<script setup lang="ts">
import { ref, watch } from 'vue'
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
import { Badge } from '@/components/ui/badge'

interface Permission {
    id: number
    name: string
}

interface Role {
    id: number
    name: string
    permissions_count: number
    users_count: number
}

interface EditableRole {
    id: number
    name: string
    permissions: number[]
}

const props = defineProps<{
    roles: Role[]
    permissions: Permission[]
    role?: EditableRole
}>()

const isEditing = ref(false)
const editingId = ref<number | null>(null)

const form = useForm({
    name: '',
    permissions: [] as number[],
})

watch(
    () => props.role,
    (newRole) => {
        if (newRole) {
            isEditing.value = true
            editingId.value = newRole.id
            form.name = newRole.name
            form.permissions = newRole.permissions
                ? [...newRole.permissions]
                : []
            window.scrollTo({ top: 0, behavior: 'smooth' })
        }
    },
    { immediate: true }
)

function submit() {
    if (isEditing.value && editingId.value !== null) {
        form.put(`/roles/${editingId.value}`, {
            onSuccess: clearForm,
        })
    } else {
        form.post('/roles', {
            onSuccess: clearForm,
        })
    }
}

function edit(role: Role) {
    router.get(`/roles/${role.id}/edit`, {}, {
        preserveState: false,
        preserveScroll: false,
    })
}

function destroy(role: Role) {
    if (confirm('Tem certeza que deseja eliminar este role?')) {
        router.delete(`/roles/${role.id}`, {
            preserveScroll: true,
        })
    }
}

function clearForm() {
    isEditing.value = false
    editingId.value = null
    form.reset()
    form.name = ''
    form.permissions = []
    router.get('/roles', {}, {
        preserveState: false,
    })
}

function togglePermission(permissionId: number) {
    const index = form.permissions.indexOf(permissionId)
    if (index > -1) {
        form.permissions.splice(index, 1)
    } else {
        form.permissions.push(permissionId)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Gestão de Roles e Permissões
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <!-- FORM -->
                <Card>
                    <CardHeader>
                        <CardTitle>
                            {{ isEditing ? 'Editar' : 'Novo' }} Role
                        </CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form class="space-y-6" @submit.prevent="submit">
                            <div class="space-y-2">
                                <Label for="name">Nome do Role *</Label>
                                <Input id="name" v-model="form.name" placeholder="Ex: Administrador" />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- PERMISSÕES -->
                            <div class="space-y-4">
                                <Label>Permissões</Label>

                                <div class="grid grid-cols-2 gap-4 rounded-lg border p-4">
                                    <div v-for="permission in permissions" :key="permission.id"
                                        class="flex items-center space-x-2">
                                        <Checkbox :id="`permission-${permission.id}`"
                                            :model-value="form.permissions.includes(permission.id)"
                                            @update:model-value="() => togglePermission(permission.id)" />

                                        <Label :for="`permission-${permission.id}`"
                                            class="cursor-pointer text-sm font-normal">
                                            {{ permission.name }}
                                        </Label>
                                    </div>
                                </div>

                                <p v-if="form.errors.permissions" class="text-sm text-destructive">
                                    {{ form.errors.permissions }}
                                </p>

                                <p class="text-sm text-gray-500">
                                    {{ form.permissions.length }} permissões selecionadas
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

                <!-- LISTAGEM -->
                <Card>
                    <CardHeader>
                        <CardTitle>Roles do Tenant</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Permissões</TableHead>
                                    <TableHead>Utilizadores</TableHead>
                                    <TableHead class="text-right">Ações</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-for="role in roles" :key="role.id">
                                    <TableCell class="font-medium">
                                        {{ role.name }}
                                    </TableCell>

                                    <TableCell>
                                        <Badge variant="secondary">
                                            {{ role.permissions_count }} permissões
                                        </Badge>
                                    </TableCell>

                                    <TableCell>
                                        <Badge variant="outline">
                                            {{ role.users_count }} utilizadores
                                        </Badge>
                                    </TableCell>

                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button size="sm" variant="outline" @click="edit(role)">
                                                Editar
                                            </Button>

                                            <Button size="sm" variant="destructive" @click="destroy(role)">
                                                Eliminar
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="roles.length === 0">
                                    <TableCell colspan="4" class="text-center text-muted-foreground">
                                        Nenhum role criado neste tenant.
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
