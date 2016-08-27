

(function($) {
    $(function() {

        var respondent = null;
        var currentBranch = '';
        var originalBranch = '';
        var currentQuestionCode = 0;
        var currentQuestionData = null;
        var currentAnswerFuture = null;
        var questions = {};
        var result = {};
        var progress = 0;
        var progressStep = 11;
        var prj_ref = '/opros';

        var emptyAll =  true;

        var container = $('#opros');
        var $answers = $('#answers');

        var ns=$.initNamespaceStorage('opros');
        var oprosStorage = ns.localStorage;

        var showQuestion = function (){
            if (currentQuestionData) {
                $answers.empty();
                $('#question').html(currentQuestionData.name);
                //console.log(currentQuestionData);
                if (currentQuestionData.is_last == 1) {
                    $('#button').hide();
                    $answers.hide();
                } else {
                    if (currentQuestionData.is_open == 1) {
                        //todo text answer
                        $answers.html('<div class="open-question-answer"><input type="text"></div>');
                        $('.open-question-answer input').focus();
                    } else if (currentQuestionData.is_multi == 1) {
                        //todo select multi answer
                        var $ul = $('<ul></ul>');
                        for (var i in currentQuestionData.answers) {
                            if (currentQuestionData.answers[i].is_open == 1) {
                                $ul.append($('<li></li>').html('<label><input type="checkbox" name="answer" value="' + currentQuestionData.answers[i].name + '"> ' + currentQuestionData.answers[i].name + '<span class="open-answer-answer"><input type="text"></span></label>').attr('question-code', currentQuestionData.answers[i].code).attr('question-branch', currentQuestionData.answers[i].branch).attr('question-open', currentQuestionData.answers[i].is_open));
                            } else {
                                $ul.append($('<li></li>').html('<label><input type="checkbox" name="answer" value="' + currentQuestionData.answers[i].name + '"> ' + currentQuestionData.answers[i].name + '</label>').attr('question-code', currentQuestionData.answers[i].code).attr('question-branch', currentQuestionData.answers[i].branch).attr('question-open', currentQuestionData.answers[i].is_open));
                            }
                        }
                        $answers.append($ul.get(0).outerHTML);
                    } else {
                        //todo select single answer
                        var $ul = $('<ul></ul>');
                        for (var i in currentQuestionData.answers) {
                            if (currentQuestionData.answers[i].is_open == 1){
                                $ul.append($('<li></li>').html('<label><input type="radio" name="answer" value="'+currentQuestionData.answers[i].name+'"> '+currentQuestionData.answers[i].name+'<span class="open-answer-answer"><input type="text"></span></label>').attr('question-code', currentQuestionData.answers[i].code).attr('question-branch', currentQuestionData.answers[i].branch).attr('question-open', currentQuestionData.answers[i].is_open));
                            } else {
                                $ul.append($('<li></li>').html('<label><input type="radio" name="answer" value="'+currentQuestionData.answers[i].name+'"> '+currentQuestionData.answers[i].name+'</label>').attr('question-code', currentQuestionData.answers[i].code).attr('question-branch', currentQuestionData.answers[i].branch).attr('question-open', currentQuestionData.answers[i].is_open));
                            }

                        }
                        $answers.append($ul.get(0).outerHTML);
                    }

                    $('#button button').html('Далее').prop('disabled', false);
                    $('#button').show();
                    $('#progress').show();
                }
            } else {
                $('#question').html('Ошибка, вопросы не загружены');
            }

        };

        var initQuestion = function() {

            //console.log('questions', questions);
            currentBranch = result.branch;
            currentQuestionCode = result.question > 0 ? result.question : 1;
            currentQuestionData = questions[currentQuestionCode+currentBranch];
            if (currentQuestionData == undefined) {
                currentQuestionData = questions[currentQuestionCode];
            }

            //console.log('question data', currentQuestionCode, currentBranch, currentQuestionData);

            if (currentQuestionData.is_last == 1) {
                progress = 100;
            } else {
                progress = (currentQuestionCode-1)*progressStep;
            }
            $('#progress .progress-bar')
                .css('width', progress+'%')
                .attr('aria-valuenow', progress)
                .html(progress+'%');
            container.find('#preload').hide();
            showQuestion();
        }

        var getQuestions = function() {
            if (oprosStorage.isEmpty('questions')) {
                $.post(prj_ref+'/ajax/poll/data', {respondent: respondent}, function(data){
                    questions = data.questions;
                    result = data.result;
                    //console.log(data.result);
                    oprosStorage.set('questions', data.questions);
                    oprosStorage.set('result', data.result);
                    //console.log('questions ajax');
                    initQuestion();
                }, "json");
            } else {
                questions = oprosStorage.get('questions');
                result = oprosStorage.get('result');
                //console.log('questions storage');
                initQuestion();
            }
        };

        var init = function(){

            if (emptyAll) {
                oprosStorage.removeAll();
            }
            if (oprosStorage.isEmpty('respondent')) {
                $.get(prj_ref+'/ajax/poll/respondent', function(data){
                    respondent = data.respondent;
                    oprosStorage.set('respondent', respondent);
                    //console.log('respondent ajax', respondent);
                    getQuestions();
                }, "json");
            } else {
                respondent = oprosStorage.get('respondent');
                //console.log('respondent storage');
                getQuestions();
            }

            //console.log(respondent);
        };

        $(document).on('input', '.open-answer-answer input', function(e) {
            $(this).parent().siblings('input').prop('checked', true);
            $(this).focus();
        });

        $(document).on('click', '#button button', function(e){
            e.preventDefault();

            var value = null;

            if (currentQuestionData.is_open == 1) {
                value = $('.open-question-answer input').val();
                if (value == '') {
                    $('#alert').html('Вы не ответили на вопрос, заполните поле ответа').show();
                    return;
                }
                currentAnswerFuture = {code: currentQuestionCode+1, branch: ''};
            } else if (currentQuestionData.is_multi == 1) {
                var $checkbox = $('input[type=checkbox]:checked');
                if ($checkbox.length > currentQuestionData.max_answer
                    || $checkbox.length == 0) {
                    //console.log($checkbox.length);
                    $('#alert').html('Проверьте, пожалуйста, не выделили ли вы больше 5 факторов.').show();
                    return;
                }
                value = $checkbox.map(function(){
                    var extraValue = '';
                    if ($(this).parents('li').attr('question-open') == 1) {
                        extraValue = $(this).siblings('span').find('input').val();
                        extraValue = extraValue ? ': '+extraValue : '';
                    }
                    return $(this).val()+extraValue;
                }).get();
                value = value.join(',');

                currentAnswerFuture = {code: $checkbox.parents('li').attr('question-code'), branch: $checkbox.parents('li').attr('question-branch')};
            } else {
                var $radio = $('input[type=radio]:checked');
                if ($radio.length < 1) {
                    $('#alert').html('Выберите вариант ответа').show();
                    return;
                }
                value = $radio.val();
                if ($radio.attr('question-open') == 1) {
                    var extraValue = $radio.siblings('span').find('input').val();
                    if (extraValue == '') {
                        $('#alert').html('Заполните поле ответа').show();
                        return;
                    }
                    value += ": " + extraValue;
                }
                currentAnswerFuture = {code: $radio.parents('li').attr('question-code'), branch: $radio.parents('li').attr('question-branch')};
            }

            $('#alert').hide();

            var that = $(this);
            that.attr('disabled', 'disabled');
            that.html('Сохранение ответа...');

            // TODO add answer , save result ajax
            result.polldata.push({code: (currentQuestionData.code+currentQuestionData.branch), value: value});
            result.branch = currentAnswerFuture.branch != '' ? currentAnswerFuture.branch : result.branch;
            result.question = currentAnswerFuture.code;
            //console.log(result,currentAnswerFuture);

            $.post(prj_ref+'/ajax/poll/save', {respondent: respondent, data: result}, function(data) {
                    if (data.status) {
                        oprosStorage.set('result', result);
                        initQuestion();
                    }
                }, "json");
        });


        init();
    });

})(jQuery);
