        @php
            $taskDisplayed = false;
          @endphp

        <perfect-scrollbar class="ps-show-limits">
          <div style="position: static;" class="ps ps--active-y">
            <div class="ps-content">
              <table class="table" id="my-day-table">
                <tbody wire:sortable="updateTaskOrder">
                  @foreach($myDay->sortBy([['due_date','asc']]) as $md)
                  <tr data-priority="{{ $md->priority_id }}" style="height: 60px"  
                    wire:sortable.item="{{ $md->id }}" wire:key="md-{{ $md->id }}">
                    <div style="display: flex; align-items: center;">
                      <td style="width: 3%; justify-content: center"><i class='bx bx-move move' wire:sortable.handle></i></td>
                    </div>
                    <td style="width: 5%;">
                      <div style="display: flex; align-items: center">
                        <i class='bx bx-circle checkcol
                        @if($md->priority_id == 4)
                            high
                        @elseif($md->priority_id == 3)
                            medium
                        @elseif($md->priority_id == 2)
                            low
                        @else
                            none
                        @endif' 
                        onclick="event.preventDefault(); document.getElementById('md-complete-{{ $md->id }}').submit()"
                          style="font-size: 20px;"></i>
                        <form action="{{ route('my_day.complete', $md) }}" id="{{ 'md-complete-'.$md->id }}" 
                          method="POST" style="display: none">
                          @csrf
                          @method('put')
                        </form>
                      </div>
                    </td>
                    <td style="width: 62%;">
                      <span>{{ $md->name }}</span> <br>
                      @if ($md->taskGroup)
                      <span style="font-size: 12px">
                        <i>
                          <a href="{{  route('task_groups.edit',$md->taskGroup->id) }}" class="group-id">
                            {{ $md->taskGroup->name }}
                          </a>
                        </i>
                      </span>
                      @endif
                    </td>
                    <td style="text-align: end; ">
                      @if($md->file)
                      <i class='bx bx-link-alt'style="color: #4c004c;background: #e5cce5;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="text-align: end; ">
                      @if($md->notes)
                      <i class='bx bx-note' style="color: #66102f;background: #ffd4e3;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="text-align: end; ">
                      @if($md->reminder)
                      <i class='bx bx-alarm' style="color: #50002f;background: #f4cce3;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="padding-top: 10px;">
                      <div class="duedate">
                        @if ($md->due_date)
                        <span>{{ \Carbon\Carbon::parse($md->due_date)->format('d M')}}</span>
                        @endif
                      </div>
                    </td>
                    <td style="width: 5%; text-align: end;">
                      <button class="editbtn" onclick="editMD({{ $md->id }})"><i class='bx bx-edit'></i></button>
                    </td>
                    <td style="width: 5%; text-align: center;">
                      <div class="dropdown">
                        <a class="dropdown"type="button"data-bs-toggle="dropdown" style="color: black">
                        <i class='bx bx-dots-vertical-rounded'></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuicon">
                          <li class="drop-custom">
                            <form action="{{ route('my_day.destroy', $md) }}" method="POST">
                              @csrf
                              @method('Delete')
                              <button type="submit" style="border: none; background:none" class="ms-2">
                                <i class='bx bx-trash' style="text-align: center"></i>
                                  <span class="ms-2">Delete</span>
                              </button>
                              </form>
                          </li>
                          @if(!$taskGroups->isEmpty())
                          <li><hr class="dropdown-divider"></li>
                          <li class="my-2">
                            <span class="ms-3">Add task to: </span>
                          </li>
                          
                            @foreach($taskGroups as $taskGroup)
                            <li class="drop-custom">
                              <button onclick="event.preventDefault(); document.getElementById('add-{{ $md->id }}-to-tg-{{ $taskGroup->id }}').submit()"
                              style="border: none; background:none; width: 140px; display:flex" class="ms-2">
                                  <span class="ms-2" style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis">
                                    {{ $taskGroup->name }}  
                                  </span>
                              </button>
                              <form id="{{ 'add-'.$md->id.'-to-tg-'.$taskGroup->id }}" action="{{ route('my_day.to-taskgroup', $md) }}" method="POST" style="display: none;">
                                @csrf
                                @method('put')
                                <input type="hidden" name="task_group_id" value="{{ $taskGroup->id }}">
                              </form>
                            </li>
                            @endforeach
              
                            @if($md->task_group_id)
                            <li class="drop-custom">
                              <button style="color: #dc3545;border: none;background: none"
                              onclick="event.preventDefault(); document.getElementById('remove-{{ $md->id }}-fr-{{ $taskGroup->id }}').submit()">
                                <i class='bx bx-x ms-2'></i>
                                <span class="ms-3">Remove </span>
                              </button>
                              <form action="{{ route('my_day.no-taskgroup', $md) }}" id="{{ 'remove-'.$md->id.'-fr-'.$taskGroup->id }}" 
                                method="POST" style="display: none">
                                @csrf
                                @method('delete')
                                <input type="hidden" name="task_group_id" value="{{ $taskGroup->id }}">
                                <input type="hidden" name="id" value="{{ $md->id }}">
                              </form>
                            </li>
                            @endif
                          @endif
                        </ul>
                      </div>
                    </td>
                    @php
                      $taskDisplayed = true;
                    @endphp
                  </tr>
                  @endforeach
              
                  @foreach($taskGroups as $taskGroup)
                  @foreach($taskGroup->tasks->where('add_to_myday', true)->where('completed', false)->sortBy([['due_date','asc']]) as $t)
                  <tr data-priority="{{ $t->priority_id }}" wire:sortable.item="{{ $t->id }}" wire:key="t-{{ $t->id }}">
                    <div style="display: flex; align-items: center">
                      <td style="width: 3%;"><i class='bx bx-move move' wire:sortable.handle></i></td>
                    </div>
                    <td style="width: 5%;">
                      <div style="display: flex; align-items: center">
                        <i class='bx bx-circle checkcol
                        @if($t->priority_id == 4)
                            high
                        @elseif($t->priority_id == 3)
                            medium
                        @elseif($t->priority_id == 2)
                            low
                        @else
                            none
                        @endif' 
                        onclick="event.preventDefault(); document.getElementById('form-complete-{{ $t->id }}').submit()"
                          style="font-size: 20px;"></i>
                        <form action="{{ route('task_groups.tasks.complete', [$taskGroup, $t]) }}" id="{{ 'form-complete-'.$t->id }}" 
                          method="POST" style="display: none">
                          @csrf
                          @method('put')
                        </form>
                      </div>
                    </td>
                    <td style="width: 62%">
                      <span>{{ $t->name }}</span> <br>
                      <span style="font-size: 12px">
                        <i>
                          <a href="{{ route('task_groups.edit',$taskGroup->id) }}" class="group-id">
                            {{ $taskGroup->name }}
                          </a>
                        </i>
                      </span>
                    </td>
                    <td style="text-align: end; ">
                      @if($t->file)
                      <i class='bx bx-link-alt' style="color: #4c004c;background: #e5cce5;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="text-align: end; ">
                      @if($t->notes)
                      <i class='bx bx-note' style="color: #66102f;background: #ffd4e3;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="text-align: end; ">
                      @if($t->reminder)
                      <i class='bx bx-alarm' style="color: #50002f;background: #f4cce3;
                      padding: 3px;border-radius: 7px;"></i>
                      @endif
                    </td>
                    <td style="padding-top: 10px; text-align: end;">
                      <div class="duedate">
                        @if ($t->due_date)
                        <span>{{ \Carbon\Carbon::parse($t->due_date)->format('d M')}}</span>
                        @endif
                      </div>
                    </td>
                    <td style="width: 5%; text-align: end;">
                      <button class="editbtn" onclick="edit({{ $t->id }})"><i class='bx bx-edit'></i></button>
                    </td>
                    <td style="width: 5%; text-align: center;">
                      <div class="dropdown">
                        <a class="dropdown"type="button"data-bs-toggle="dropdown" style="color: black">
                        <i class='bx bx-dots-vertical-rounded'></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuicon" style="width: 200px">
                          <li class="drop-custom">
                            <form action="{{ route('task_groups.tasks.destroy', [$taskGroup, $t]) }}" method="POST">
                              @csrf
                              @method('Delete')
                              <button type="submit" style="border: none; background:none" class="ms-2">
                                <i class='bx bx-trash' style="text-align: center"></i>
                                  <span class="ms-1">Delete</span>
                              </button>
                              </form>
                          </li>
                          <li class="drop-custom mt-1">
                            @if($t->add_to_myday)
                            <button style="color: #dc3545;border: none;background: none"
                              onclick="event.preventDefault(); document.getElementById('remove-fr-md-{{ $t->id }}').submit()">
                              <i class='bx bx-x ms-2'></i>
                              <span class="ms-1">Added to My Day</span>
                            </button>
                            <form action="{{ route('task_groups.tasks.removefrmyday',[$taskGroup, $t]) }}" id="{{ 'remove-fr-md-'.$t->id }}" 
                              method="POST" style="display: none">
                              @csrf
                              @method('delete')
                            </form>
                            @else
                            <button onclick="event.preventDefault(); document.getElementById('add-to-md-{{ $t->id }}').submit()"
                              style="border: none;background: none">
                              <i class='bx bx-sun ms-2' style="text-align: center"></i>
                              <span class="ms-1">Add to My Day</span>
                            </button>
                            <form action="{{ route('task_groups.tasks.addtomyday',[$taskGroup, $t]) }}" id="{{ 'add-to-md-'.$t->id }}" 
                              method="POST" style="display: none">
                              @csrf
                              @method('put')
                            </form>
                            @endif
                          </li>
                        </ul>
                      </div>
                    </td>
                    @php
                      $taskDisplayed = true;
                    @endphp
                  </tr>
                  @endforeach
                  @endforeach
                </tbody>
              </table>
            </div>
            
          </div>
        </perfect-scrollbar>

        @if(!$taskDisplayed)
          <div class="scroll-area d-flex justify-content-center align-items-center flex-column mt-5">
            <img src="{{ asset("img/8264.jpg") }}" style="width:350px">
              <span style="text-align: center; font-size: 20px; color: #a8bbbf">
                Take control of your day by prioritizing your <br>
                tasks and staying focused. 
              </span>
              
          </div>
          @endif

@livewireScripts
<script>
    
</script>
