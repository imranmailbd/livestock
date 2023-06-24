//Livestock Name
    const tagRow = cTag('div',{ 'class': 'flex', 'style': "text-align: left;" });
        const tagTitle = cTag('div',{ 'class': 'columnXS12 columnSM4' });
            const tagLabel = cTag('label',{ 'for': 'tag','id': 'lbtag' });
            tagLabel.innerHTML = Translate('Tag');
                let requireSpan = cTag('span',{ 'class': 'required' });
                requireSpan.innerHTML = '*';
            tagLabel.appendChild(requireSpan);
        tagTitle.appendChild(tagLabel);
    tagRow.appendChild(tagTitle);
        const tagField = cTag('div',{ 'class': 'columnXS12 columnSM8' });
        tagField.appendChild(cTag('input',{ 'type': 'text','class': 'form-control','name': 'tag','id': 'tag','value': data.tag,'maxlength': '100' }));
        tagField.appendChild(cTag('span',{ 'class': 'error_msg','id': 'errmsg_tag' }));
    tagRow.appendChild(tagField);
divCol7.appendChild(tagRow);