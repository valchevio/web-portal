<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks Portal</title>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .fixed-header {
            position: sticky;
            top: 0;
            z-index: 40;
        }
    </style>
</head>

<body class="bg-gray-100">
    <div x-data="taskApp()">

        <div class="bg-white shadow-md fixed-header mb-6">
            <div class="container mx-auto px-4 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">Tasks Portal</h1>
                    </div>


                    <div class="w-full max-w-md mx-4">
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Search tasks..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div class="flex items-center">
                            <div class="mr-2 flex flex-col items-end text-right" x-show="lastFetchTime">
                                <div class="text-gray-400 text-[0.65rem]" title="Last fetch timestamp">
                                    <span x-text="formatLastFetchTime()"></span>
                                </div>
                                <div class="text-gray-400 text-[0.65rem]" title="Time until next refresh">
                                    <span x-text="formatNextRefreshTime()"></span>
                                </div>
                            </div>
                            <button @click="fetchTasks(true)" class="p-2 text-gray-500 hover:text-blue-600 hover:bg-gray-100 rounded-full transition focus:outline-none" title="Refresh data">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                        </div>
                        <div class="text-sm bg-gray-100 p-2 rounded shadow-sm" x-show="userInfo">
                            <p class="font-semibold relative">
                                <span x-text="userInfo.displayName || 'Unknown User'"></span>
                                <span x-show="userInfo.businessUnit" x-text="' (' + userInfo.businessUnit + ')'"></span>
                                <span class="absolute top-0 left-0 w-full h-full opacity-0 hover:opacity-100 transition-opacity duration-200 bg-gray-100 text-gray-500 text-center" x-text="'ID: ' + (userInfo.personalNo || 'N/A')"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-4 mt-16">
            <div class="grid grid-cols-12 gap-6">

                <div class="col-span-12 lg:col-span-9">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 w-[70%]" @click="sortBy('title')">
                                        Title
                                        <span x-show="sortColumn === 'title' && sortDirection === 'asc'">▲</span>
                                        <span x-show="sortColumn === 'title' && sortDirection === 'desc'">▼</span>
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 w-[30%]" @click="sortBy('parentTaskID')">
                                        Parent
                                        <span x-show="sortColumn === 'parentTaskID' && sortDirection === 'asc'">▲</span>
                                        <span x-show="sortColumn === 'parentTaskID' && sortDirection === 'desc'">▼</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(task, index) in sortedTasks" :key="index">
                                    <tr :style="{ borderLeft: '10px solid ' + (task.colorCode || task.ColorCode || task.color || '#ccc') }" :class="{'bg-yellow-100': highlightedParentId === getTaskId(task), 'hover:bg-gray-50 cursor-pointer': true}" @click="showTaskDetail(task)" :data-task-id="getTaskId(task)">
                                        <td class="px-6 py-4 text-sm">
                                            <div>
                                                <span class="text-gray-400 mr-2 inline-block" x-text="task.task || task.Task || task.taskName || task.id || 'N/A'"></span>
                                                <span class="font-medium text-gray-900" x-text="task.title || task.Title || task.name || 'N/A'"></span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <template x-if="task.parentTaskID && hasParent(task)">
                                                <button @click.stop="highlightParent(task.parentTaskID)" class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs hover:bg-blue-200 focus:outline-none flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                    </svg>
                                                    <span x-text="task.parentTaskID"></span>
                                                </button>
                                            </template>
                                            <template x-if="task.parentTaskID && !hasParent(task)">
                                                <button class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs flex items-center" title="Parent task does not exist in the current dataset">
                                                    <svg class="h-3 w-3 text-red-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <span x-text="task.parentTaskID"></span>
                                                </button>
                                            </template>
                                            <template x-if="!task.parentTaskID">
                                                <span class="text-gray-400">-</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>


                                <template x-if="sortedTasks.length === 0">
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 text-center text-sm">
                                            <template x-if="isLoading">
                                                <div class="py-8">
                                                    <svg class="animate-spin h-8 w-8 text-blue-500 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <p class="text-gray-600 font-medium">Refreshing data from the API...</p>
                                                    <p class="text-gray-500 text-sm mt-2">Please wait while we fetch the latest tasks.</p>
                                                </div>
                                            </template>
                                            <template x-if="!isLoading">
                                                <p class="text-gray-500 py-4">No tasks found matching your search.</p>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div class="col-span-12 lg:col-span-3">
                    <div class="bg-white p-6 rounded-lg shadow-md h-full">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Image Upload</h2>
                        <p class="text-gray-600 mb-4">This feature allows you to upload and preview images. The uploaded images are not linked to tasks.</p>
                        <button @click="modalOpen = true" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition w-full">Upload Image</button>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="taskDetailModalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-transition>

            <div class="fixed inset-0 bg-black bg-opacity-50"></div>

            <div class="relative flex items-center justify-center min-h-screen p-4">
                <div @click.away="taskDetailModalOpen = false" class="relative bg-white rounded-lg shadow-xl w-full max-w-[90vw] h-[90vh] p-6 z-60 overflow-hidden flex flex-col">
                    <div>
                        <div class="flex justify-between items-center">
                            <div class="flex-grow">
                                <div class="flex items-center flex-wrap gap-2">
                                    <h3 class="text-lg font-medium flex items-center flex-wrap">
                                        <template x-if="selectedTask">
                                            <div class="flex items-center mr-2">
                                                <span class="text-gray-400 mr-2 font-light" x-text="selectedTask.task || selectedTask.Task || selectedTask.taskName || selectedTask.id || 'N/A'"></span>
                                                <span x-text="selectedTask.title || selectedTask.Title || selectedTask.name || 'Task Details'"></span>
                                            </div>
                                        </template>
                                        <template x-if="!selectedTask">
                                            <span>Task Details</span>
                                        </template>
                                    </h3>
                                    <template x-if="selectedTask && selectedTask.parentTaskID">
                                        <div class="inline-flex items-center">
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                <span x-text="selectedTask.parentTaskID"></span>
                                            </span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <button @click="taskDetailModalOpen = false" class="text-gray-400 hover:text-gray-500 ml-2 p-1 rounded-full hover:bg-gray-100">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>


                        <div class="w-full h-[2px] mt-3 mb-4 rounded-full" :style="{ backgroundColor: selectedTask ? (selectedTask.colorCode || selectedTask.ColorCode || selectedTask.color || '#ccc') : '#ccc' }"></div>
                    </div>

                    <div class="mt-4 flex-grow overflow-y-auto">
                        <div class="grid grid-cols-12 gap-8 h-full">

                            <div class="col-span-12 lg:col-span-8">
                                <template x-if="selectedTask">
                                    <div class="h-full">
                                        <div class="text-sm font-medium text-gray-700 mb-2">Description</div>
                                        <pre class="whitespace-pre-wrap bg-gray-50 p-6 rounded text-sm w-full h-[calc(100%-1.5rem)]" x-text="selectedTask.description || selectedTask.Description || ''"></pre>
                                    </div>
                                </template>
                            </div>


                            <div class="col-span-12 lg:col-span-4">
                                <template x-if="selectedTask">
                                    <div class="space-y-3 text-xs">

                                        <div class="space-y-3">

                                            <div>
                                                <div class="text-xs font-medium text-gray-500 mb-1">Color Code</div>
                                                <div class="flex items-center bg-gray-50 px-2 py-1 rounded inline-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                                    </svg>
                                                    <div class="w-5 h-5 rounded mr-2" :style="{ backgroundColor: selectedTask.colorCode || selectedTask.ColorCode || selectedTask.color || '#ccc' }"></div>
                                                    <span class="text-xs" x-text="selectedTask.colorCode || selectedTask.ColorCode || selectedTask.color || '#ccc'"></span>
                                                </div>
                                            </div>


                                            <div>
                                                <div class="text-xs font-medium text-gray-500 mb-1">Wage Type</div>
                                                <template x-if="selectedTask.wageType">
                                                    <div class="inline-flex items-center px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <span x-text="selectedTask.wageType"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedTask.wageType">
                                                    <div class="text-gray-400 text-xs">—</div>
                                                </template>
                                            </div>


                                            <div>
                                                <div class="text-xs font-medium text-gray-500 mb-1">Business Unit</div>
                                                <template x-if="selectedTask.businessUnit">
                                                    <div class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                        </svg>
                                                        <span x-text="selectedTask.businessUnit"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!selectedTask.businessUnit">
                                                    <div class="text-gray-400 text-xs">—</div>
                                                </template>
                                            </div>
                                        </div>


                                        <template x-for="(value, key) in selectedTask" :key="key">
                                            <template x-if="shouldShowInModal(key, value) && key !== 'wageType' && key !== 'businessUnit'">
                                                <div class="border-b border-gray-100 pb-2">
                                                    <div class="text-xs font-medium text-gray-500" x-text="getFormattedKey(key)"></div>
                                                    <div class="mt-1 text-sm" x-html="formatTaskDetail(key, value)"></div>
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" x-transition>

            <div class="fixed inset-0 bg-black bg-opacity-50"></div>

            <div class="relative flex items-center justify-center min-h-screen p-4">
                <div @click.away="modalOpen = false" class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-60">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Upload Image</h3>
                        <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-4">
                        <button @click="$refs.fileInput.click()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition w-full">Select Image</button>
                        <input x-ref="fileInput" @change="handleFileSelect" type="file" accept="image/*" class="hidden">
                    </div>

                    <div x-show="selectedImage" class="mt-4">
                        <img :src="selectedImage" class="max-w-full h-auto rounded">
                    </div>
                </div>
            </div>
        </div>

    </div>
    </div>

    <script src="/js/tasks.js"></script>
</body>

</html>