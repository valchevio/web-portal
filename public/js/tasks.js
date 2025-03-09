function taskApp() {
    return {
        tasks: [],
        userInfo: null,
        searchQuery: '',
        isLoading: true,
        modalOpen: false,
        taskDetailModalOpen: false,
        parentTaskModalOpen: false,
        selectedTask: null,
        parentTask: null,
        selectedImage: null,
        lastFetchTime: null,
        cacheExpirationMs: 60 * 60 * 1000, // 60 minutes in milliseconds
        timeUntilRefresh: null,
        refreshInterval: null,
        highlightedParentId: null,
        sortColumn: null,
        sortDirection: null,
        
        init() {
            // Try to load from cache first
            this.loadFromCache();
            
            // Set up auto-refresh every 60 minutes
            setInterval(() => this.fetchTasks(), this.cacheExpirationMs);
            
            // Set up countdown timer that updates every second
            this.updateTimeUntilRefresh();
            this.refreshInterval = setInterval(() => this.updateTimeUntilRefresh(), 1000);
        },
        
        updateTimeUntilRefresh() {
            if (!this.lastFetchTime) return;
            
            const now = new Date().getTime();
            const nextRefreshTime = this.lastFetchTime + this.cacheExpirationMs;
            this.timeUntilRefresh = Math.max(0, nextRefreshTime - now);
            
            // If it's time to refresh and we haven't yet, do it now
            if (this.timeUntilRefresh === 0) {
                this.fetchTasks();
            }
        },
        
        loadFromCache() {
            try {
                // Get data from localStorage
                const cachedData = localStorage.getItem('tasksData');
                
                if (cachedData) {
                    const { tasks, userInfo, timestamp } = JSON.parse(cachedData);
                    const now = new Date().getTime();
                    this.lastFetchTime = timestamp;
                    
                    if (now - timestamp < this.cacheExpirationMs) {
                        this.tasks = tasks;
                        this.userInfo = userInfo;
                        this.isLoading = false;
                        return true;
                    }
                }
            } catch (error) {}
            
            this.fetchTasks();
            return false;
        },
        
        formatLastFetchTime() {
            if (!this.lastFetchTime) return '';
            
            const date = new Date(this.lastFetchTime);
            return date.toLocaleString();
        },
        
        formatNextRefreshTime() {
            if (!this.timeUntilRefresh) return 'Calculating...';
            
            // Convert milliseconds to minutes and seconds
            const totalSeconds = Math.floor(this.timeUntilRefresh / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            
            if (minutes > 0) {
                // If more than 1 minute, show minutes only
                return `Refreshing in ${minutes} min.`;
            } else {
                // If less than 1 minute, show seconds only
                return `Refreshing in ${seconds} sec.`;
            }
        },
        
        sortBy(column) {
            // If clicking the same column, toggle direction
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // If clicking a new column, set it as the sort column and default to ascending
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },
        
        hasParent(task) {
            if (!task.parentTaskID) return false;
            
            return this.tasks.some(t => 
                t.id === task.parentTaskID || 
                t.task === task.parentTaskID || 
                t.taskId === task.parentTaskID
            );
        },
        
        fetchTasks(forceRefresh = false) {
            // If not forcing refresh, and we have recent data in cache, use that
            if (!forceRefresh && this.lastFetchTime) {
                const now = new Date().getTime();
                const timeSinceLastFetch = now - this.lastFetchTime;
                
                if (timeSinceLastFetch < this.cacheExpirationMs) {
                    return;
                }
            }
            
            this.isLoading = true;
            
            if (forceRefresh) {
                this.tasks = [];
            }
            
            fetch('/api/tasks')
                .then(response => response.json())
                .then(data => {
                    let tasksArray = [];
                    
                    if (Array.isArray(data)) {
                        tasksArray = data;
                    } else if (typeof data === 'object') {
                        if (data.tasks) {
                            tasksArray = Array.isArray(data.tasks) ? data.tasks : [];
                            
                            if (data.userInfo) {
                                this.userInfo = data.userInfo;
                            }
                        } else {
                            const possibleArrayProps = Object.entries(data)
                                .filter(([_, value]) => Array.isArray(value));
                                
                            if (possibleArrayProps.length > 0) {
                                const [propName, arrayData] = possibleArrayProps[0];
                                tasksArray = arrayData;
                            }
                        }
                    }
                    
                    this.tasks = tasksArray;
                    
                    this.lastFetchTime = new Date().getTime();
                    
                    this.updateTimeUntilRefresh();
                    
                    this.saveToCache(tasksArray, this.lastFetchTime);
                    
                    this.isLoading = false;
                })
                .catch(() => {
                    this.tasks = [];
                    this.isLoading = false;
                });
        },
        
        saveToCache(tasks, timestamp) {
            try {
                const cacheData = {
                    tasks: tasks,
                    userInfo: this.userInfo,
                    timestamp: timestamp
                };
                localStorage.setItem('tasksData', JSON.stringify(cacheData));
            } catch (error) {
                // Silent fail on cache storage errors
            }
        },
        
        showTaskDetail(task) {
            this.selectedTask = task;
            this.taskDetailModalOpen = true;
        },
        
        openParentTaskModal(parentTaskId) {
            // Find the parent task in the tasks array
            const parent = this.tasks.find(t => 
                t.id === parentTaskId || 
                t.task === parentTaskId || 
                t.Task === parentTaskId || 
                t.taskId === parentTaskId
            );
            
            if (parent) {
                this.parentTask = parent;
                this.parentTaskModalOpen = true;
            }
        },
        
        highlightParent(parentTaskId) {
            this.highlightedParentId = parentTaskId;
            setTimeout(() => {
                this.highlightedParentId = null;
            }, 2000);
            
            const parentRow = document.querySelector(`[data-task-id="${parentTaskId}"]`);
            if (parentRow) {
                parentRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        
        isParentTask(task) {
            return this.tasks.some(t => 
                t.parentTaskID && 
                (t.parentTaskID === task.id || 
                 t.parentTaskID === task.task || 
                 t.parentTaskID === task.taskId)
            );
        },
        
        getTaskId(task) {
            return task.id || task.task || task.taskId;
        },
        
        getTaskParent(task) {
            if (!task.parentTaskID) return null;
            
            return this.tasks.find(t => 
                t.id === task.parentTaskID || 
                t.task === task.parentTaskID || 
                t.taskId === task.parentTaskID
            );
        },
        
        shouldShowInModal(key, value) {
            const excludedKeys = [
                'task', 'Task', 'taskName', 'id',
                'title', 'Title', 'name',
                'parentTaskID',
                'sort',
                'colorCode', 'ColorCode', 'color',
                'description', 'Description',
                'preplanningBoardQuickSelect',
                'workingTime',
                'isAvailableInTimeTrackingKioskMode'
            ];
            
            if (key === 'BusinessUnitKey') {
                return false;
            }
            
            return !excludedKeys.includes(key);
        },
        
        getFormattedKey(key) {
            const keyMap = {
                'wageType': 'Wage Type',
                'businessUnit': 'Business Unit'
            };
            
            return keyMap[key] || key;
        },
        
        formatTaskDetail(key, value) {
            if (value === null || value === undefined) return 'N/A';
            
            if (typeof value === 'object') {
                return JSON.stringify(value, null, 2);
            }
            
            return value;
        },
        
        get filteredTasks() {
            if (!this.searchQuery.trim()) {
                return this.tasks;
            }
            
            const query = this.searchQuery.toLowerCase();
            return this.tasks.filter(task => {
                const searchableProps = [
                    task.task, task.Task, task.taskName, task.id,
                    task.title, task.Title, task.name,
                    task.description, task.Description, task.desc,
                    task.colorCode, task.ColorCode, task.color
                ];
                
                if (searchableProps.every(prop => prop === undefined)) {
                    return Object.values(task).some(value => {
                        if (typeof value === 'string') {
                            return value.toLowerCase().includes(query);
                        }
                        return false;
                    });
                }
                
                return searchableProps.some(prop => {
                    if (prop && typeof prop === 'string') {
                        return prop.toLowerCase().includes(query);
                    }
                    return false;
                });
            });
        },
        
        get sortedTasks() {
            if (!this.sortColumn) {
                return this.filteredTasks;
            }
            
            const tasks = [...this.filteredTasks];
            
            return tasks.sort((a, b) => {
                let valueA, valueB;
                
                if (this.sortColumn === 'task') {
                    valueA = a.task || a.Task || a.taskName || a.id || '';
                    valueB = b.task || b.Task || b.taskName || b.id || '';
                } else if (this.sortColumn === 'title') {
                    valueA = a.title || a.Title || a.name || '';
                    valueB = b.title || b.Title || b.name || '';
                } else if (this.sortColumn === 'parentTaskID') {
                    valueA = a.parentTaskID || '';
                    valueB = b.parentTaskID || '';
                } else {
                    valueA = a[this.sortColumn] || '';
                    valueB = b[this.sortColumn] || '';
                }
                
                valueA = String(valueA).toLowerCase();
                valueB = String(valueB).toLowerCase();
                
                if (this.sortDirection === 'asc') {
                    return valueA.localeCompare(valueB);
                } else {
                    return valueB.localeCompare(valueA);
                }
            });
        },
        
        
        handleFileSelect() {
            const file = this.$refs.fileInput.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.selectedImage = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        
    };
}